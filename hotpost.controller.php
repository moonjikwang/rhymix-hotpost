<?php

/**
 * @file hotpost.controller.php
 * @brief Controller for the hotpost (인기글) module.
 */
class HotpostController extends Hotpost
{
	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Intercept document.getDocumentList (before). When the current request
	 * activates one of the configured filter sets for this board, replace
	 * the default query with our filtered query via $obj->use_alternate_output.
	 *
	 * @param object $obj args passed to DocumentModel::getDocumentList
	 * @return BaseObject
	 */
	public function triggerFilterDocumentList(&$obj)
	{
		// Need at least one resolvable module_srl on the args.
		if (empty($obj->module_srl))
		{
			return new BaseObject();
		}
		$module_srls = is_array($obj->module_srl) ? $obj->module_srl : array($obj->module_srl);
		$module_srls = array_values(array_filter(array_map('intval', $module_srls)));
		if (!count($module_srls))
		{
			return new BaseObject();
		}

		// Pick the filter set whose query parameter is active for this board.
		$filter = HotpostModel::getActiveFilter($module_srls[0]);
		if (!$filter)
		{
			return new BaseObject();
		}

		$args = new stdClass;
		$args->module_srl = (count($module_srls) === 1) ? $module_srls[0] : $module_srls;
		$args->statusList = array('PUBLIC');

		if ($filter->min_readed_count > 0)
		{
			$args->min_readed_count = $filter->min_readed_count;
		}
		if ($filter->min_voted_count > 0)
		{
			$args->min_voted_count = $filter->min_voted_count;
		}
		if ($filter->min_comment_count > 0)
		{
			$args->min_comment_count = $filter->min_comment_count;
		}

		if ($filter->period_days > 0)
		{
			$args->start_regdate = date('YmdHis', strtotime('-' . $filter->period_days . ' days'));
		}

		// Honour category filter coming from the board view.
		if (!empty($obj->category_srl))
		{
			$args->category_srl = $obj->category_srl;
		}

		// Exclude notices like the board does for its regular list.
		if (!empty($obj->except_notice))
		{
			$args->is_notice = 'N';
		}

		// Inherit pagination/sort from the caller (boardView).
		$args->sort_index = $obj->sort_index ?? 'list_order';
		$args->order_type = $obj->order_type ?? 'asc';
		$args->list_count = intval($obj->list_count ?? 20) ?: 20;
		$args->page_count = intval($obj->page_count ?? 10) ?: 10;
		$args->page = intval($obj->page ?? 1) ?: 1;

		$query_id = ($filter->combine_mode === 'or')
			? 'hotpost.getHotDocumentListOr'
			: 'hotpost.getHotDocumentListAnd';

		$output = executeQueryArray($query_id, $args);
		if ($output->toBool())
		{
			$obj->use_alternate_output = $output;
		}

		return new BaseObject();
	}
}
