<?php

/**
 * @file hotpost.model.php
 * @brief Model for the hotpost (인기글) module.
 *
 * Configuration consists of an ordered list of named "filter sets".
 * Each filter set declares its own URL query parameter, conditions
 * (minimum view/vote/comment counts), combine mode, period and target boards.
 * Visiting a target board with ?<query_param>=Y activates that filter.
 */
class HotpostModel extends Hotpost
{
	/**
	 * Initialize the model.
	 *
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Get the module configuration with defaults and migration applied.
	 *
	 * Old (single-filter) configs are converted in-memory to the new
	 * filters-array layout so callers always see the same shape. The
	 * change is persisted the next time the admin saves the form.
	 *
	 * @return object
	 */
	public static function getConfig(): object
	{
		$config = ModuleModel::getModuleConfig('hotpost');
		if (!is_object($config))
		{
			$config = new stdClass;
		}

		$filters = $config->filters ?? null;

		// Migration: old single-filter shape → wrap into one filter set.
		if (!is_array($filters) && (isset($config->target_modules) || isset($config->min_readed_count) || isset($config->min_voted_count) || isset($config->min_comment_count)))
		{
			$legacy = new stdClass;
			$legacy->name = '인기글';
			$legacy->query_param = trim(strval($config->query_param ?? '')) ?: 'hotpost';
			$legacy->min_readed_count = intval($config->min_readed_count ?? 0);
			$legacy->min_voted_count = intval($config->min_voted_count ?? 0);
			$legacy->min_comment_count = intval($config->min_comment_count ?? 0);
			$legacy->combine_mode = in_array($config->combine_mode ?? '', array('and', 'or'), true) ? $config->combine_mode : 'and';
			$legacy->period_days = intval($config->period_days ?? 0);
			$legacy->target_modules = is_array($config->target_modules ?? null) ? $config->target_modules : array();
			$filters = array($legacy);
		}

		if (!is_array($filters))
		{
			$filters = array();
		}

		$normalized = array();
		foreach ($filters as $f)
		{
			$normalized[] = self::_normalizeFilter($f);
		}
		$config->filters = $normalized;
		return $config;
	}

	/**
	 * Normalize one filter set to a predictable shape and types.
	 *
	 * @param mixed $f
	 * @return object
	 */
	protected static function _normalizeFilter($f): object
	{
		if (!is_object($f) && !is_array($f))
		{
			$f = new stdClass;
		}
		$f = (object) $f;

		$out = new stdClass;
		$out->name = trim(strval($f->name ?? ''));
		$out->query_param = preg_replace('/[^a-zA-Z0-9_]/', '', strval($f->query_param ?? '')) ?: '';
		$out->min_readed_count = max(0, intval($f->min_readed_count ?? 0));
		$out->min_voted_count = max(0, intval($f->min_voted_count ?? 0));
		$out->min_comment_count = max(0, intval($f->min_comment_count ?? 0));
		$out->combine_mode = in_array($f->combine_mode ?? '', array('and', 'or'), true) ? $f->combine_mode : 'and';
		$out->period_days = max(0, intval($f->period_days ?? 0));
		$targets = is_array($f->target_modules ?? null) ? $f->target_modules : array();
		$out->target_modules = array_values(array_unique(array_map('intval', $targets)));
		return $out;
	}

	/**
	 * Get the configured filter sets.
	 *
	 * @return array
	 */
	public static function getFilters(): array
	{
		$config = self::getConfig();
		return $config->filters;
	}

	/**
	 * Whether the given filter has at least one numeric condition configured.
	 *
	 * @param object $filter
	 * @return bool
	 */
	public static function filterHasAnyCondition(object $filter): bool
	{
		return $filter->min_readed_count > 0 || $filter->min_voted_count > 0 || $filter->min_comment_count > 0;
	}

	/**
	 * Whether the given module_srl is in the filter's target list.
	 *
	 * @param object $filter
	 * @param int $module_srl
	 * @return bool
	 */
	public static function filterIncludesModule(object $filter, int $module_srl): bool
	{
		return in_array($module_srl, array_map('intval', $filter->target_modules), true);
	}

	/**
	 * Find the filter set that should be applied to the current request for
	 * the given module_srl. A filter matches when its query_param is set to
	 * 'Y' on the current request AND the module is in its target list AND
	 * it has at least one condition configured.
	 *
	 * @param int $module_srl
	 * @return object|null
	 */
	public static function getActiveFilter(int $module_srl): ?object
	{
		foreach (self::getFilters() as $filter)
		{
			if ($filter->query_param === '')
			{
				continue;
			}
			if (Context::get($filter->query_param) !== 'Y')
			{
				continue;
			}
			if (!self::filterIncludesModule($filter, $module_srl))
			{
				continue;
			}
			if (!self::filterHasAnyCondition($filter))
			{
				continue;
			}
			return $filter;
		}
		return null;
	}

	/**
	 * Build the URL that activates a given filter on a given board.
	 * Passing '' as the first argument resets current request params
	 * so the URL does not inherit admin's module=admin&act=... .
	 *
	 * @param object $filter
	 * @param string $mid
	 * @return string
	 */
	public static function getFilterUrl(object $filter, string $mid): string
	{
		if ($filter->query_param === '')
		{
			return '';
		}
		return getNotEncodedUrl('', 'mid', $mid, $filter->query_param, 'Y');
	}
}
