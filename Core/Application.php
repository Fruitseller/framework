<?php

namespace framework\Core;

use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;

class Application {
	public function run() {
		$this->initWhoops();
	}

	private function initWhoops() {
		$whoops = new WhoopsRun();
		$handler = new WhoopsPrettyPageHandler();
		$whoops->pushHandler($handler)->register();

		return $this;
	}
}
