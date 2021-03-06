<?php

declare(ticks = 1);

require_once(__DIR__ . '/../modules/forkdaemon-php/fork_daemon.php');

/* setup forking daemon */
$server = new fork_daemon();
$server->max_work_per_child_set(3);
$server->register_child_run('process_child_run');
$server->helper_process_spawn('helper');

/* add work units */
$data_set = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
$server->addwork($data_set);

/* process work blocking mode */
$server->process_work(true);

/* registered call back function */
function process_child_run($data_set)
{
  echo "I'm child working on: " . implode(",", $data_set) . PHP_EOL;
}

function helper()
{
	echo "Starting helper" . PHP_EOL;

	while(true)
	{
		echo "Checking to make sure that image processor is still running." . PHP_EOL;
		sleep(3);
	}
}
