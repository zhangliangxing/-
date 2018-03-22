<?php


namespace framework;


class Upload
{
	//��Ա����
	//�ļ��ϴ�·��
	protected $path = './public/upload/';
	//�����ϴ���׺
	protected $allowSuffix = ['jpg', 'jpeg', 'png', 'gif', 'wbmp', 'bmp'];
	//�����ϴ���mime
	protected $allowMime = ['image/png', 'image/gif', 'image/jpeg', 'image/wbmp'];
	//�����ϴ����ļ�size
	protected $maxSize = 20000000000000000;
	//�Ƿ����������
	protected $isRandName = true;
	//�Ƿ���������Ŀ¼
	protected $isDatePath = true;
	//�����ļ�ǰ׺
	protected $prefix = 'up_';

	//�Զ���Ĵ������ʹ�����Ϣ
	protected $errorNumber;
	protected $errorInfo;

	//Ҫ������ļ���Ϣ
	//�ļ���
	protected $oldName;
	//�ļ���׺
	protected $suffix;
	//�ļ���С
	protected $size;
	//�ļ�mime
	protected $mime;
	//�ļ���ʱ·��
	protected $tmpName;

	//�ļ������ֺ���·��
	protected $newName;
	protected $newPath;

	//���췽����ʼ��һ����Ա����
	function __construct($arr = [])
	{
		foreach($arr as $key => $value) {
			$this->setOption($key, $value);
		}
	}

	//�жϸ�$key�ǲ����ҵĳ�Ա���ԣ�����ǣ���ô����֮
	protected function setOption($key, $value)
	{
		//�õ����еĳ�Ա����
		$keys = array_keys(get_class_vars(__ClASS__));
		//���$key�ǳ�Ա���ԡ�����֮
		if (in_array($key, $keys)) {
			$this->$key = $value;
		}
	}

	//д�ļ��ϴ�����
	//����������input���name����ֵ
	function uploadFile($key)
	{
		//�ж���û������path
		if (empty($this->path)) {
			$this->setOption('errorNumber', -1);
			return false;
		}

		//�жϸ�·���Ƿ���ڡ���д
		if (!$this->checkDir()) {
			$this->setOption('errorNumber', -2);
			return false;
		}
		//echo 111;
		//�ж�$_FILES�����error��Ϣ�Ƿ�Ϊ0�����Ϊ0����ȡ��Ϣ���浽��Ա������
		$error = $_FILES[$key]['error'];
		if ($error) {
			$this->setOption('errorNumber', $error);
			return false;
		} else {
			//��ȡ�ļ������Ϣ���浽��Ա������
			$this->getFileInfo($key);
		}

		//�жϴ�С�Ƿ���ϡ�mime�����Ƿ���ϡ���׺�Ƿ����
		if ((!$this->checkSize()) || (!$this->checkMime()) || (!$this->checkSuffix())) {
			return false;
		}
		//�õ��µ��ļ������õ��µ��ļ�·��
		$this->newName = $this->createNewName();
		$this->newPath = $this->createNewPath();
		//var_dump($this->newName);
		//var_dump($this->newPath);
		//�ж��Ƿ����ϴ��ļ����ƶ��ļ�
		if (is_uploaded_file($this->tmpName)) {
			if (move_uploaded_file($this->tmpName, $this->newPath.$this->newName)) {
				$this->setOption('errorNumber', 0);
				return $this->newPath . $this->newName;
			} else {
				$this->setOption('errorNumber', -7);
				return false;
			}
		} else {
			$this->setOption('errorNumber', -6);
			return false;
		}
	}

	protected function checkDir()
	{
		//�ļ��в����ڻ��߲���Ŀ¼�������ļ���
		if (!file_exists($this->path) || !is_dir($this->path)) {
			//����3���Ƿ񴴽��м�Ŀ¼
			return mkdir($this->path, 0755, true);
		}

		//�ж��ļ��Ƿ��д
		if (!is_writable($this->path)) {
			return chmod($this->path, 0755);
		}

		return true;
	}

	//��ȡ�ļ������Ϣ
	protected function getFileInfo($key)
	{
		//�õ��ļ�����
		$this->oldName = $_FILES[$key]['name'];
		//�õ��ļ�mime
		$this->mime = $_FILES[$key]['type'];
		//�õ��ļ���ʱ·��
		$this->tmpName = $_FILES[$key]['tmp_name'];
		//�õ��ļ���С
		$this->size = $_FILES[$key]['size'];
		//�õ��ļ���׺
		$this->suffix = pathinfo($this->oldName)['extension'];
	}

	//�ж��ļ���С����
	protected function checkSize()
	{
		if ($this->size > $this->maxSize) {
			$this->setOption('errorNumber', -3);
			return false;
		}
		return true;
	}

	//�ж��ļ�mime����
	protected function checkMime()
	{
		if (!in_array($this->mime, $this->allowMime)) {
			$this->setOption('errorNumber', -4);
			return false;
		}
		return true;
	}

	//�ж��ļ���׺�Ƿ����
	protected function checkSuffix()
	{
		if (!in_array($this->suffix, $this->allowSuffix)) {
			$this->setOption('errorNumber', -5);
			return false;
		}
		return true;
	}

	//�õ��µ��ļ���
	protected function createNewName()
	{
		if ($this->isRandName) {
			$name = $this->prefix.uniqid().'.'.$this->suffix;
		} else {
			$name = $this->prefix.$this->oldName;
		}
		return $name;
	}

	//�õ��µ�·��
	protected function createNewPath()
	{
		if ($this->isDatePath) {
			$path = $this->path.date('y/m/');
			if (!file_exists($path)) {
				mkdir($path, 0755, true);
			}
			return $path;
		} else {
			return $this->path;
		}
	}

	//дget���������ⲿ�õ�����źʹ�����Ϣ
	function __get($name)
	{
		if ($name == 'errorNumber') {
			return $this->errorNumber;
		} else if ($name == 'errorInfo') {
			return $this->getErrorInfo();
		}
	}

	//����Ŷ�Ӧ�Ĵ�����Ϣ
	protected function getErrorInfo()
	{
		//-1 =>�ļ�·��û������
		//-2 =���ļ�����Ŀ¼����Ȩ�޴���
		//-3 => �ļ���Ϣ��̫��
		//-4 => �ļ�mime���Ͳ�����
		//-5 => �ļ���׺������
		//-6 => �ļ����ϴ��ļ�
		switch ($this->errorNumber) {
			case 0:
				$str = '�ļ��ϴ��ɹ�';
				break;
			case 1:
				$str = '�ļ�����php.ini����';
				break;
			case 2:
				$str = '�ļ�����html����';
				break;
			case 3:
				$str = '�����ļ��ϴ�';
				break;
			case 4:
				$str = 'û���ļ��ϴ�';
				break;
			case 6:
				$str = '�Ҳ�����ʱ�ļ�';
				break;
			case 7:
				$str = '�ļ�д��ʧ��';
				break;
			case -1:
				$str = '�ļ�·��û������';
				break;
			case -2:
				$str = '�ļ�����Ŀ¼����Ȩ�޴���';
				break;
			case -3:
				$str = '�ļ���Ϣ��̫��';
				break;
			case -4:
				$str = '�ļ�mime���Ͳ�����';
				break;
			case -5:
				$str = '�ļ���׺������';
				break;
			case -6:
				$str = '�ļ������ϴ��ļ�';
				break;
			case -7:
				$str = '�ļ��ϴ�ʧ��';
				break;
		}
		return $str;
	}
}
