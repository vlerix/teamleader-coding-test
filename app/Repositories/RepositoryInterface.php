<?php

namespace App\Repositories;


interface RepositoryInterface
{
	public function exists($id);
	public function Find($id);

}