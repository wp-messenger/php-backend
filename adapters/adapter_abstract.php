<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adapter_abstract
 *
 * @author JoseVega
 */
abstract class LiveChat_Adapter {

	abstract function is_user_logged_in();

	abstract function config();

	abstract function start();

	abstract function get_current_user_id();
	
	abstract function get_mysql_time_now();

	abstract function emit_event($event, $data);
	
	abstract function run_filter($event, $data, $extra_data);
}
