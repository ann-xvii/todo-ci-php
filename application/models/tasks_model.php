<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tasks_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}


	/*
	this serves two functions
		1. to display all tasks when a user first visits the site
		2. to display all tasks when a user enters a new task in the form
	*/
	function get_tasks() {
		$result = $this->db->get('tasks');

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/*
	this changes the tasks.task_status value in the database from either todo or done
		done -> a task set to done appears struck through in the list
		todo -> not struck through and displayed normally 

	the change_task_status() function takes two arguments, $task_id and $save_data valuese are passed from the tasks controller's status function

	$task_id:

	the value of $task_id is set when the user clicks on either It's Done or Still Todo in the views/tasks/view.php

	the fourth parameter of the uri segment of either option is the primary key (tasks.task_id) of the task in the tasks table

	by using the Codeigniter function $this->uri->segment(4), we grab the value and store in a $task_id local variable

	$save_data:

	the $save_data value is populated in the tasks controller. it contains only one item, task_status, that is populated in the status() function with the third parameter of the uri segment:
	*/

	function change_task_status($task_id, $save_data) {
		$this->db->where('task_id', $task_id);
		if ($this->db->update('tasks', $save_data)) {
			return true;
		} else {
			return false;
		}
	}

	/*
	this saves a task to the database when a user submits the form
	
	the save_task() function accepts one argument--an array of data

	this data is supplied by the tasks controller's index() function
	the function will save a task to the tasks table, returning true if successful and false if an error occurs
	*/
	function save_task($save_data) {
		if ($this->db->insert('tasks', $save_data)) {
			return true;
		} else {
			return false;
		}
	}


	/*
	get_task this fetches an individual task form the tasks table
	
	the get_task function takes one argument $task_id (that is, the primary key of the task in the database); it is supplied by the tasks controller's delete() function, which uses it to supply information about the task in the delete confirmation form

	the user clicks on Delete in the views/tasks/view.php file
	the third parameter of which is the task's primary key

	the tasks controller's delete() function will then grab that ID from the URI with the $this->uri->segment(3) CodeIgniter function

	this ID is passed to the get_task() model function, which will return the details of the task in the database or false if no ID is found:
	*/

	function get_task($id) {
		$this->db->where('task_id', $id);
		$result = $this->db->get('tasks');
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}


	/*
	delete() deletes a task from the tasks table

	the delete() function performs an operation on the database to remove a task
	delete() accepts one argument--the ID of the task, which is the primary key of that task:
	*/

	function delete($id) {
		$this->db->where('task_id', $id);
		$result = $this->db->delete('tasks');
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
}