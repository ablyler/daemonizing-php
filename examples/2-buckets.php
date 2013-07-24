<?php

require_once(__DIR__ . '/../modules/forkdaemon-php/fork_daemon.php');

/* setup forking daemon */
$server = new fork_daemon();
$server->max_children_set(5);
$server->max_work_per_child_set(3);

test_bucket();

function test_bucket()
{
	global $server;

	define("IMAGES", 1);
	define("DOCUMENTS", 2);

	$server->add_bucket(IMAGES);
	$server->add_bucket(DOCUMENTS);
	$server->max_children_set(2, IMAGES);
	$server->max_children_set(5, DOCUMENTS);
	$server->register_child_run("process_child_run_1", IMAGES);
	$server->register_child_run("process_child_run_2", DOCUMENTS);

	$data_set = array();
	for($i=0; $i<10; $i++) $data_set[] = $i;

	/* add work to bucket 1 */
	shuffle($data_set);
	$server->addwork($data_set, "", IMAGES);

	/* add work to bucket 2 */
	shuffle($data_set);
	$server->addwork($data_set, "", DOCUMENTS);

	/* wait until all work allocated */
	while ($server->work_sets_count(IMAGES) > 0 || $server->work_sets_count(DOCUMENTS) > 0)
	{
		if ($server->work_sets_count(IMAGES) > 0) $server->process_work(false, IMAGES);
		if ($server->work_sets_count(DOCUMENTS) > 0) $server->process_work(false, DOCUMENTS);
		sleep(1);
	}

	/* wait until all children finish */
	while ($server->children_running() > 0)
	{
		sleep(1);
	}
}

/*
 * CALLBACK FUNCTIONS
 */

/* registered call back function */
function process_child_run_1($data_set, $identifier = "")
{
	echo "I'm an Image child working on: " . implode(",", $data_set) . ($identifier == "" ? "" : " (id:$identifier)") . "\n";
	sleep(rand(4,8));
}

/* registered call back function */
function process_child_run_2($data_set, $identifier = "")
{
	echo "I'm a Document child working on: " . implode(",", $data_set) . ($identifier == "" ? "" : " (id:$identifier)") . "\n";
	sleep(rand(4,8));
}

