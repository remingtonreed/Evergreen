<?php
/*
*	Copyright (C) 2006-2009 NaturalCodeProject
*	All Rights Reserved
*	
*	@author Daniel Baldwin
*	
*	Description: Controller class for all user controllers.
*
*/

abstract class Controller
{
	protected $params;
	protected $settings;
	protected $formhandler;
	protected $branch_name;
	protected $config;
	private $view_overridden = false;
	
	final function __construct ()
	{
		## Construct Code
		$this->branch_name = Factory::get_config()->get_branch_name();
		$this->config = Factory::get_config();
		$this->params = $this->config->get_working_uri();
		
		$this->formhandler = new Formhandler($this);
		$this->designer = new Designer();
		
		## Show View ##
		$this->show_view();
	}
	
	final private function show_view ()
	{
		## Set up the actual page
		$full_page = $this->run_view();
		
		## First Designer Fix
		$this->designer->do_fixes($full_page);
		
		## Form Fix
		$this->formhandler->decode($full_page);
		
		## Second Designer Fix
		$this->designer->do_fixes($full_page);
		
		## Output Page
		echo $full_page;
	}
	
	final private function run_view()
	{
		ob_start();
		$error = false;
		if (empty($this->params['view'])) $this->params['view'] = "index";
		
		if ($this->view_exists($this->params['view']) || (!$this->view_exists($this->params['view']) && isset($this->bounceback) && method_exists($this, $this->bounceback['check']) && method_exists($this, $this->bounceback['bounce'])))
		{
			if (isset($this->bounceback) && !$this->view_exists($this->params['view']))
			{
				$view = $this->params['view'];
				$values = array_values($this->params);
				$this->params = array_combine(array_keys($this->params), array_slice(array_merge(array($values[0]), array($this->bounceback['bounce']),array_slice($values, 1)), 0, count(array_keys($this->params))));
				
				if (!call_user_func(array($this, $this->bounceback['check'])))
				{
					if (!$this->view_exists($view))
					{
						$error = true;
						header("HTTP/1.0 404 Not Found");
						//header("Location: ".URI_ROOT."/error");
						exit;
					}
				}
			}
			$filter_name = strtolower($this->filter);
			if (isset($this->filter))
			{
				if (!isset($this->filter_only) || (isset($this->filter_only) && in_array($this->params['view'], $this->filter_only)))
				{
					$this->$filter_name();
				}
			}
			
			ob_start();
				call_user_func(array($this, strtolower($this->params['view'])));
				if (!$this->view_overridden) $this->get_view($this->params['view']);
			$this->content_for_layout = ob_get_clean();
			
		}
		else
		{
			$error = true;
			header("HTTP/1.0 404 Not Found");
			//header("Location: ".URI_ROOT."/error");
			exit;
		}
		
		if(!empty($this->layout) && !$error)
		{
			$this->render_layout($this->layout);
		}
		else
		{
			echo $this->content_for_layout;
		}
		
		$full_page = ob_get_clean();
		
		return $full_page;
	}
	
	final protected function get_view ($name, $controller="", $override = false)
	{
		$this->view_overridden = $override;
		if (empty($controller)) $controller = $this->params['controller'];
		if ((empty($this->branch_name) && (@include("views/".strtolower($controller)."/{$name}.inc")) == true) || (!empty($this->branch_name) && (@include("branches/{$this->branch_name}/views/".strtolower($controller)."/{$name}.inc")) == true))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	final protected function render_view ($name, $controller="", $override = false)
	{
		$this->view_overridden = $override;
		if (empty($controller)) $controller = $this->params['controller'];
		if (strtolower(get_class($this)) == $controller)
		{
			$instance_func = $this;
		}
		else
		{
			$new_controller_name = ucwords($controller);
			$arr_keys = array_keys($this->params);
			$new_params = $params;
			$new_params[$arr_keys[0]] = $controller;
			$new_params[$arr_keys[1]] = $name;
			ob_start();
				$instance_func = new $new_controller_name($this->settings, $new_params, $this->raw_uri);
			ob_end_clean();
		}
		
		if (method_exists($instance_func, $name))
		{
			$instance_func->$name();
			$instance_func->get_view($name);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	final protected function view_exists ($name, $controller="")
	{
		if (empty($controller)) $controller = $this->params['controller'];
		
		if (method_exists($this, $name))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	final protected function render_layout ($name)
	{
		$content_for_layout = $this->content_for_layout;
		if (!empty($this->branch_name) && (@include("branches/{$this->branch_name}/views/layouts/{$name}.inc")) == true)
		{
			return 1;
		}
		else
		{
			if ((@include("views/layouts/{$name}.inc")) == true)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}

?>