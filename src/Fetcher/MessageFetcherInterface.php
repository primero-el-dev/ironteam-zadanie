<?php

namespace App\Fetcher;

interface MessageFetcherInterface
{
	public function fetch(): void;
}