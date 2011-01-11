<?php

class Thread extends Controller {

	function Thread()
	{
		parent::Controller();

		$this->load->helper(array('url', 'date', 'form', 'content_render'));
		$this->load->library(array('form_validation', 'pagination'));
		$this->load->model('thread_dal');
	}
	
	// if the just throw in /thread into the address bar
	// throw them home
	function index()
	{
		redirect('/');
	}
	
	function load($thread_id)
	{
		// if they roll in with something unexpected
		// send them home
		if (!is_numeric($thread_id))
			redirect('/');
		
		// grabbing the thread information
		$query = $this->thread_dal->get_thread_information($thread_id);
		
		// does it exist?
		if ($query->num_rows === 0)
			redirect('/');
		
		// alright we're clear, set some data for the view
		$data = array(
			'title' => $query->row()->subject,
			'thread_id' => $thread_id
		);
		
		// we're going to go ahead and do the form processing for the reply now
		// if they're submitting data, we're going to refresh the page anyways
		// so theres no point in running the query below the form validation
		$this->form_validation->set_rules('content', 'Content', 'required');
		
		// if a comment was submitted
		if ($this->form_validation->run())
		{
			$content = _ready_for_save($this->form_validation->set_value('content'));
			
			$this->thread_dal->new_comment(array(
				'thread_id' => $thread_id,
				'content' => $content
			));
			
			redirect(uri_string());
		}
		
		$display = $this->session->userdata('comments_shown') == false ? 50 : (int)$this->session->userdata('comments_shown');
		
		$pseg = 0;
		$base_url = '';
		$limit_start = 0;
		
		for($i=1; $i<=count($this->uri->segments); ++$i)
		{
			$base_url .= '/'. $this->uri->segments[$i];
			
			if ($this->uri->segments[$i] == 'p')
			{
				if (isset($this->uri->segments[$i+1]) && is_numeric($this->uri->segments[$i+1]))
				{
					$pseg = $i+1;
					$limit_start = (int)$this->uri->segments[$i+1];
					
					break;
				}
			}
		}
		
		if ($pseg === 0) $base_url .= '/p';
		
		$data['comment_result'] = $this->thread_dal->get_comments($thread_id, $limit_start, $display);
		
		$data['total_comments'] = $this->thread_dal->comment_count($thread_id);
		
		$this->pagination->initialize(array(
			'base_url' => $base_url,
			'total_rows' => $data['total_comments'],
			'uri_segment' => $pseg,
			'per_page' => $display
		)); 
		
		$data['pagination'] = $this->pagination->create_links();
		
		$this->load->helper('content_render');
		
		$this->load->view('shared/header');
		$this->load->view('thread', $data);
		$this->load->view('shared/footer');
	}
	
	function _ready_content($content)
	{
		$content = nl2br($content);
		
		return $content;
	}
}

/* End of file thread.php */
/* Location: ./application/controllers/thread.php */