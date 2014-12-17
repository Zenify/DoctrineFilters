<?php

namespace Zenify\DoctrineFilters\Tests\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Zenify\DoctrineFilters\Filter;


class ActiveFilter extends Filter
{

	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
	{
		return "$targetTableAlias.active = 1";
	}

}