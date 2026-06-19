<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Tests\Fixtures\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelLikes\Tests\Fixtures\Data\TestPostData;

final class TestPostDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(TestPostData::class);
    }
}
