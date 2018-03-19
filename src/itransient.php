<?php

namespace kdaviesnz\amazon;


interface ITransient {

	public function save(String $transient, $value, int $expiration ):bool;
	public function fetch(String $transient);

}