<?php

class EWRporta_ControllerPublic_Thread extends XFCP_EWRporta_ControllerPublic_Thread
{
	public $perms;

	public function actionIndex()
	{
		$response = parent::actionIndex();
		$options = XenForo_Application::get('options');
		$format = $this->_input->filterSingle('format', XenForo_Input::STRING);
		$response->params['thread']['format'] = $format;

		if ($format != 'default')
		{
			if ($response instanceof XenForo_ControllerResponse_View
				&& $options->EWRporta_globalize['article']
				&& (
					in_array($response->params['forum']['node_id'], $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums())
					|| $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($response->params['thread']['thread_id'])
				))
			{
				$response->params['isArticle'] = true;
				$response->params['layout1'] = 'article-'.$response->params['thread']['thread_id'];
				$response->params['layout2'] = 'article';
				$response->params['layout3'] = 'portal';
				$response->params['categories'] = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinks($response->params['thread']);

				return $this->responseView('XenForo_ViewPublic_Thread_View', 'EWRporta_ArticleView', $response->params);
			}

			if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['thread'])
			{
				$response->params['layout1'] = 'thread-'.$response->params['thread']['thread_id'];
				$response->params['layout2'] = 'thread-forum-'.$response->params['forum']['node_id'];
				$response->params['layout3'] = 'thread';
			}
		}

		if (!empty($response->params['page']) && $response->params['page'] > 1)
		{
			unset($response->params['posts'][$response->params['thread']['first_post_id']]);
		}

		return $response;
	}

	public function actionCategory()
	{
		if (!$this->perms['promote']) { return $this->responseNoPermission(); }

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'thread_id' => XenForo_Input::UINT,
				'newlinks' => XenForo_Input::ARRAY_SIMPLE,
				'catlinks' => XenForo_Input::ARRAY_SIMPLE,
				'oldlinks' => XenForo_Input::ARRAY_SIMPLE
			));

			$this->getModelFromCache('EWRporta_Model_Categories')->updateCategories($input);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $input));
		}

		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

		$viewParams = array(
			'thread' => $thread,
			'catlinks' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinks($thread),
			'categories' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryNolinks($thread),
		);

		return $this->responseView('EWRporta_ViewPublic_Category', 'EWRporta_Category', $viewParams);
	}

	public function actionPromote()
	{
		if (!$this->perms['promote']) { return $this->responseNoPermission(); }

		$input = $this->_input->filter(array(
			'thread_id' => XenForo_Input::UINT,
			'promote_date' => XenForo_Input::UINT,
			'promote_icon' => XenForo_Input::STRING,
			'attach_data' => XenForo_Input::UINT,
			'image_data' => XenForo_Input::STRING,
			'medio_data' => XenForo_Input::UINT,
			'date' => XenForo_Input::STRING,
			'hour' => XenForo_Input::UINT,
			'mins' => XenForo_Input::UINT,
			'ampm' => XenForo_Input::STRING,
			'zone' => XenForo_Input::STRING,
			'delete' => XenForo_Input::STRING,
		));

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($input['thread_id']);

		if ($this->_request->isPost())
		{
			$this->getModelFromCache('EWRporta_Model_Promotes')->updatePromotion($input);
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentSlider'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentAccordion'));
		}
		else
		{
			$threadPromote = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($thread['thread_id']);

			$visitor = XenForo_Visitor::getInstance();
			$datetime = $threadPromote ? $threadPromote['promote_date'] : $thread['post_date'];
			$datetime = new DateTime(date('r', $datetime));
			$datetime->setTimezone(new DateTimeZone($visitor['timezone']));
			$datetime = explode('.', $datetime->format('Y-m-d.h.i.A.T'));

			$datetime = array(
				'date' => $datetime[0],
				'hour' => $datetime[1],
				'mins' => $datetime[2],
				'meri' => $datetime[3],
				'zone' => $datetime[4]
			);

			switch ($datetime['hour'])
			{
				case "01":	$datetime['time']['01'] = 'selected="selected"'; break;
				case "02":	$datetime['time']['02'] = 'selected="selected"'; break;
				case "03":	$datetime['time']['03'] = 'selected="selected"'; break;
				case "04":	$datetime['time']['04'] = 'selected="selected"'; break;
				case "05":	$datetime['time']['05'] = 'selected="selected"'; break;
				case "06":	$datetime['time']['06'] = 'selected="selected"'; break;
				case "07":	$datetime['time']['07'] = 'selected="selected"'; break;
				case "08":	$datetime['time']['08'] = 'selected="selected"'; break;
				case "09":	$datetime['time']['09'] = 'selected="selected"'; break;
				case "10":	$datetime['time']['10'] = 'selected="selected"'; break;
				case "11":	$datetime['time']['11'] = 'selected="selected"'; break;
				default:	$datetime['time']['12'] = 'selected="selected"'; break;
			}

			switch ($datetime['meri'])
			{
				case "PM":	$datetime['ampm']['pm'] = 'selected="selected"'; break;
				default:	$datetime['ampm']['am'] = 'selected="selected"'; break;
			}

			$icons = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteIcons($thread);

			switch ($threadPromote['promote_icon'])
			{
				case "avatar":		$icons['avatar'] = 'checked="checked"';		break;
				case "attach":		$icons['attach'] = 'checked="checked"';		break;
				case "image":		$icons['image'] = 'checked="checked"';		break;
				case "medio":		$icons['medio'] = 'checked="checked"';		break;
				case "disabled":	$icons['disabled'] = 'checked="checked"';	break;
				default:			$icons['default'] = 'checked="checked"'; 	break;
			}

			$viewParams = array(
				'thread' => $thread,
				'icons' => $icons,
				'threadPromote' => $threadPromote,
				'datetime' => $datetime,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('EWRporta_ViewPublic_Promote', 'EWRporta_Promote', $viewParams);
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $thread));
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRporta_Model_Perms')->getPermissions();
	}
}