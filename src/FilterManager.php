<?php

declare(strict_types=1);

/*
 * This file is part of Zenify
 * Copyright (c) 2012 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace Zenify\DoctrineFilters;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Zenify\DoctrineFilters\Contract\ConditionalFilterInterface;
use Zenify\DoctrineFilters\Contract\FilterInterface;
use Zenify\DoctrineFilters\Contract\FilterManagerInterface;


final class FilterManager implements FilterManagerInterface
{

	/**
	 * @var FilterInterface[]
	 */
	private $filters = [];

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var bool
	 */
	private $areFiltersEnabled = FALSE;


	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addFilter(string $name, FilterInterface $filter)
	{
		$this->filters[$name] = $filter;
	}


	/**
	 * {@inheritdoc}
	 */
	public function enableFilter(string $name)
	{
		if ( ! isset($this->filters[$name])) {
			return;
		}

		$filter = $this->filters[$name];
		if ($filter instanceof ConditionalFilterInterface && ! $filter->isEnabled()) {
			return;
		}

		$this->addFilterToEnabledInFilterCollection($name, $filter);
	}


	/**
	 * {@inheritdoc}
	 */
	public function enableFilters()
	{
		if ($this->areFiltersEnabled) {
			return;
		}

		foreach ($this->filters as $name => $filter) {
			if ($filter instanceof ConditionalFilterInterface && ! $filter->isEnabled()) {
				continue;
			}

			$this->addFilterToEnabledInFilterCollection($name, $filter);
		}

		$this->areFiltersEnabled = TRUE;
	}


	private function addFilterToEnabledInFilterCollection($name, FilterInterface $filter)
	{
		$filterCollection = $this->entityManager->getFilters();

		$filterCollectionReflection = new ReflectionClass($filterCollection);
		$enabledFiltersReflection = $filterCollectionReflection->getProperty('enabledFilters');
		$enabledFiltersReflection->setAccessible(TRUE);

		$enabledFilters = $enabledFiltersReflection->getValue($filterCollection);
		$enabledFilters[$name] = $filter;
		$enabledFiltersReflection->setValue($filterCollection, $enabledFilters);
	}

}
