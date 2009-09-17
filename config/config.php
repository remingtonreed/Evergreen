<?php
	## System Setup ##
	Config::register("System.mode", "development");
	
	## URI Setup ##
	Config::register("URI.useModRewrite", true);
	Config::register("URI.useDashes", true);
	Config::register("URI.forceDashes", true);
	
	Config::register("URI.map", array(
		"controller"=>"main",
		"view"=>"index",
		"action"=>"",
		"id"=>""
	));
	
	## Errors Setup ##
	Config::register("Errors.generalErrorMessage", "An error occured. Please contact admin@example.com");
	Config::register("Errors.logDirectory", "public/log");
	Config::register("Errors.404", "/error404");
	
	## Database Setup ##
	Config::register("Database.host", "localhost");
	Config::register("Database.username", "root");
	Config::register("Database.password", "root");
	Config::register("Database.database", "hooktest");
	Config::register("Database.driver", "MySQL");
	
	## Routes ##
	Config::registerRoute("/test(.*)", "/testing/index/$1");
	Config::registerRoute("/oranges(.*)", "/developer/main/oranges/$1");
	Config::registerRoute("/pickles(.*)", "/developer/main/pickles/$1");
?>