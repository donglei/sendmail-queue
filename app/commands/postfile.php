<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class postfile extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'postfile';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '上传文件';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$ch = curl();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	private function postFile($model, $path)
	{
		$uploadTokens = [
			//photo 1001
			'11' => '69025f5e6c7dd0c1157e0daf94e9cef5:Cs0CwAf9DKb99_ZKMiFDkhVyk0E=:eyJkZWFkbGluZSI6MTQ0MTYxOTcyNCwiY2FsbGJhY2tCb2R5IjpbXX0=',
			//kids  1000
			'10' => '16ea8772e383bb56b9bf0be63b3dd1fc:KNP1pZl6Z97t0S8Tv7Ro07g7G9w=:eyJkZWFkbGluZSI6MTQ0MTYyODAxMywiY2FsbGJhY2tCb2R5IjpbXX0='
		];

		$cfile = new CURLFile($path, 'image/' . pathinfo($path, PATHINFO_EXTENSION));
		// print_r($cfile);exit;
		$data = [
			'appid' => '',
			'x:uid' => $model['uid'],
			'uploadToken' => $uploadTokens[$model['appid']],
			'filename' => basename($path),
			'sha1' => '',
			'file' => $cfile
		];
		 $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://upload.putaocloud.com/upload');
        curl_setopt($curl, CURLOPT_POST, 1); //post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //设置传送的参数
        curl_setopt($curl, CURLOPT_HEADER, false); //设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //要求结果为字符串


		$response = curl_exec($curl);
		
		if(curl_errno($curl)){
			$this->error('post file error hash:' . $model['hash'] . ' path:' . $path . ' error string:' . curl_errno($curl));
			return false;
		}
		curl_close($curl);

		if($response){
			$info = json_decode($response, true);
			if (!empty($info['error_code'])) {
				$this->error('post file error hash:' . $model['hash'] . ' path:' . $path . ' json string:' . $response);
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
