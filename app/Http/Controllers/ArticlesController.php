<?php

namespace App\Http\Controllers;

use App\Api\QuintypeClient;

class ArticlesController extends Controller
{
	public function __construct(QuintypeClient $qc)
	{
		$this->qc = $qc;
	}

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('articles.index');
	}
}