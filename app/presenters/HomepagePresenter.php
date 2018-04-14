<?php

namespace App\Presenters;


class HomepagePresenter extends BasePresenter
{
	public function renderDefault()
	{
		$this->redirect('TestIssue:Default');
	}
}
