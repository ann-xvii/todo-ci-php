<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tasks extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Tasks_model');
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger">', '</div>');
	}

	/*
	The index function displays a list of tasks and handles the form submission, validation, error checking and so on
	*/
	public function index() {

		// set validation rules for the form
		$this->form_validation->set_rules('task_desc', $this->lang->line('tasks_task_desc'), 'required|min_length[1]|max_length[255]');
		$this->form_validation->set_rules('task_due_d', $this->lang->line('task_due_d'), 'min_length[1]|max_length[2]');
		$this->form_validation->set_rules('task_due_m', $this->lang->line('task_due_m'), 'min_length[1]|max_length[2]');
		$this->form_validation->set_rules('task_due_y', $this->lang->line('task_due_y'), 'min_length[4]|max_length[4]');

		// if there were errors in the form, or if it is the first time the page is accessed, then we'll build the form elements, defining their settings and be ready to draw them in the view

		if ($this->form_validation->run() == FALSE) {

			$page_data['job_title'] = array('name' => 'job_title', 'class' => 'form_control', 'id' => 'job_title', 'value' => set_value('job_title', ''), 'max_length' => '100', 'size' => '35');
			$page_data['task_desc'] = array('name' => 'task_desc', 'class' => 'form_control', 'id' => 'task_desc', 'value' => set_value('task_desc', ''), 'max_length' => '255', 'size' => '35');
			$page_data['task_due_d'] = array('name' => 'task_due_d', 'class' => 'form_control', 'id' => 'task_due_d', 'value' => set_value('task_due_d', ''), 'max_length' => '100', 'size' => '35');
			$page_data['task_due_m'] = array('name' => 'task_due_m', 'class' => 'form_control', 'id' => 'task_due_m', 'value' => set_value('task_due_m', ''), 'max_length' => '100', 'size' => '35');
			$page_data['task_due_y'] = array('name' => 'task_due_y', 'class' => 'form_control', 'id' => 'task_due_y', 'value' => set_value('task_due_y', ''), 'max_length' => '100', 'size' => '35');


			// next, fetch all tasks in the database and store them in the $page_data['query'] array, and we will send this array to the tasks/view.php file where it will be looped over using foreach($query->result as $row) -- where each task will be written out in a table along with the Done, Todo and Delete options

			$page_data['query'] = $this->Tasks_model->get_tasks();

			$this->load->view('templates/header');
			$this->load->view('nav/top_nav');
			$this->load->view('tasks/view', $page_data);
			$this->load->view('templates/footer');

		} else {
			// if there were no errors in the form, then we try to create the task in the database
			// first we look to see whether the user has tried to set a due date for the task
			// we do this by looking for the data fields in the post array
			// we require all three, day, month and year, items to create a due date, so we check to see whether all three have been set

			if ($this->input->post('task_due_y') && $this->input->post('task_due_m') && $this->input->post('task_due_d')) {
				$task_due_date = $this->input->post('task_due_y') . '-' . $this->input->post('task_due_m') . '-' . $this->input->post('task_due_d');
			} else {
				$task_due_date = null;
			}

			// we then create an array to pass to the save_task() function of the Tasks_model
			// the $save_data array contains the task description, any date that might have been applied or null value, and a default value for the task_status, which is initially set to todo
			$save_data = array(
				'task_desc' => $this->input->post('task_desc'),
				'task_due_date' => $task_due_date,
				'task_status' => 'todo'
				);
			// the $save_data array is then sent to the save_task() function of Tasks_model; this function will return true if the save operation was successful or false if there was an error
			// whatever the outcome we'll set a message using th $this->session->set_flashdata() CodeIgniter function with a success message or an error message *the ccontent for these messages is in the language file
			// on second thought, screw the session method, for now
			// well... try it
			if ($this->Tasks_model->save_task($save_data)) {
				$this->session->set_flashdata('flash_message', $this->lang->line('create_success_okay'));
			} else {
				$this->session->set_flashdata('flash_message', $this->lang->line('create_success_fail'));
			}
			// redirect to the tasks controller's index function, which will display the tasks (and hopefully, the one just created by the user)
			redirect ('tasks');
		}

	} 


	/*
	The status function is used to change a task status from done to todo
	*/
	public function status() {
		$page_data['task_status'] = $this->uri->segment(3);
		$task_id = $this->uri->segment(4);

		// we take the third and fourth parameters, and send them to the change_task_status() function of the Tasks_model
		// the change_task_status() function will return true if the update was successful or false if there was an error
		// we set a message to the user using the $this->session->set_flashdata() Codeigniter function

		if ($this->Tasks_model->change_task_status($task_id, $page_data)) {
			$this->session->set_flashdata('flash_message', $this->lang->line('status_change_success'));
		} else {
			$this->session->set_flashdata('flash_message', $this->lang->line('status_change_fail'));
		}

		redirect ('tasks');
	}

	/*
	The delete function does 2 things
		1. displays information about the task to the user so that they are able to decide whether they really want to delete the task
		2. also processes the deletion of that task should it be confirmed by the user
	*/
	// first we set the validation rules for the form, this is the form that the user uses to confirm the deletion
	public function delete() {
		$this->form_validation->set_rules('id', $this->lang->line('task_id'), 'required|min_length[1]|max_length[11]|integer|is_natural');

		// the form can be accessed by the user clicking on Delete (get) or submitting the form (post)
		// so the task ID can be supplied either from the URI in the case of Delete or in a hidden form element in the form

		// so we check whether the form is being posted or accessed for the first time and grab the ID from either post or get
		if ($this->input->post()) {
			$id = $this->input->post('id');
		} else {
			$id = $this->uri->segment(3);
		}

		$data['page_heading'] = 'Confirm delete?';
		if ($this->form_validation->run() == FALSE) {

			// we then send the ID to the get_task() function of the Tasks_model, which will return the details of the task as a database object
			// this is saved in $data['query'] and sent to the tasks/delete.php view file, where the user is asked to confirm whether they wish to really delete the task
			$data['query'] = $this->Tasks_model->get_task($id);
			$this->load->view('templates/header', $data);
			$this->load->view('nav/top_nav', $data);
			$this->load->view('tasks/delete', $data);
			$this->load->view('templates/footer', $data);
		} else {
			// if there were no errors with the form submission, then we call the delete() function of Tasks_model so that the task is deleted

			if ($this->Tasks_model->delete($id)) {
				redirect('tasks');
			}
		}
	}


}