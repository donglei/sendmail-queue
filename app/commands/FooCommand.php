<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
		use Pheanstalk\Pheanstalk;

class FooCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'foo';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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
		$beanstalk_config = Config::get('beanstalk');

		$pheanstalk = new Pheanstalk($beanstalk_config['host'],$beanstalk_config['port']);

		while ($job = $pheanstalk->reserveFromTube($beanstalk_config['tube'])) {
			Log::info("Send Mail do With:: job id:" .  $job->getId() . ' data:' . $job->getData());
			$data = json_decode($job->getData(), true);
			if ($data['to'] == '' || $data['data'] == '') {
				Log::info("Send Mail empty:: job id:" .  $job->getId());
				continue;
			}
			$send_mail = [
				'host' => 'smtp.putao.com',
				'port' => "587",
				'username' => 'no-reply@putao.com',
				'password' => 'putao@12345',
			];
			Mail::send('emails.msg', array('data' => $data['data']), function($message) use($data)
			{
			    $message->to($data['to'], $data['to'])->subject($data['subject']);
			});
			Log::info("Send Mail release:: job id:" .  $job->getId());
			$pheanstalk->delete($job);
		}

 
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

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
