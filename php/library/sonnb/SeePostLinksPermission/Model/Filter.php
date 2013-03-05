<?php

/**
 * Product: sonnb - See post's links permission
 * Version: 1.1.2
 * Date: 28th Jan 2013
 * Author: sonnb
 * Website: www.sonnb.com - www.UnderWorldVN.com
 * License: You might not copy or redistribute this addon.
 */
class sonnb_SeePostLinksPermission_Model_Filter
{
	private static  $_instance;
	
	public $xenOptions;
	
	public $applicableNodes;
	
	public $checkCondition;
	
	public $postCondition;
	
	public $visitor;
	
	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self();
				
			self::$_instance->xenOptions = XenForo_Application::get('options');
				
			self::$_instance->applicableNodes = self::$_instance->xenOptions->sonnb_SPLP_nodes;
				
			self::$_instance->checkCondition = self::$_instance->xenOptions->sonnb_SPLP_options;
			
			self::$_instance->postCondition = self::$_instance->xenOptions->sonnb_SPLP_postOption;
				
			self::$_instance->visitor = XenForo_Visitor::getInstance();
		}
	
		return self::$_instance;
	}
	
	public function isApplicableForum(array $forum)
	{
		if (empty($forum))
		{
			$forum = array('node_id' => 0);
		}
		
		if (in_array($forum['node_id'], $this->applicableNodes))
		{
			return true;
		}
		
		return false;
	}
	
	public function isContainLinks(array $message)
	{
		return preg_match_all('/\[URL(=[^\]]+?)?\].*?\[\/URL\]/si', $message['message'], $matches);
	}
	
	public function processMessages(&$messages)
	{
		$totalMatchCount = 0;
		if ($messages)
		{
			foreach ($messages as &$message)
			{
				$totalMatchCount += $this->processMessage($message);
			}
		}
		
		return $totalMatchCount;
	}
	
	public function processMessage(&$message, $remove = null)
	{
		$totalMatchCount = 0;
		if (!$this->visitor->hasPermission('sonnb_SPLP', 'bypass') &&
				$message['user_id'] != $this->visitor['user_id'] && 
				(($this->postCondition == 'first' && $message['position'] == 0) ||
					$this->postCondition == 'all'))
		{
			switch ($this->checkCondition)
			{	
				case "post_like":
					if ($this->visitor['user_id'] == 0 || 
							(empty($message['like_date']) || 
							($message['canLike'] && $message['like_date'] === null)))
					{
						$matchCount = preg_match_all('/\[URL(=[^\]]+?)?\].*?\[\/URL\]/si', $message['message'], $match);

						$totalMatchCount += $matchCount;
						if ($matchCount)
						{
							if (!$remove)
							{
								if ($this->visitor['user_id'])
								{
									$replaceMessage = new XenForo_Phrase('sonnb_SPLP_message_like_to_user');
								}
								else
								{
									$replaceMessage = new XenForo_Phrase('sonnb_SPLP_message_like_to_guest', array(
											'login_link' => XenForo_Link::buildPublicLink('full:login'),
											'current' => $this->visitor['message_count']
									));
								}
								
								//$replaceMessage = $replaceMessage->render();
							}
							else
							{
								$replaceMessage = '';
							}
							
							foreach ($match as $matchEle)
							{
								$message['message'] = str_replace($matchEle, $replaceMessage, $message['message']);
							}
						}
					}
					break;
				case "post_count":
					if ($this->visitor['message_count'] < $this->xenOptions->sonnb_SPLP_minPostCount)
					{
						$matchCount = preg_match_all('/\[URL(=[^\]]+?)?\].*?\[\/URL\]/si', $message['message'], $match);
						
						$totalMatchCount += $matchCount;
						if ($matchCount)
						{
							if (!$remove)
							{
								$replaceMessage = new XenForo_Phrase('sonnb_SPLP_message_post_count', array(
									'total' => $this->xenOptions->sonnb_SPLP_minPostCount,
									'current' => $this->visitor['message_count']
								));
								$replaceMessage = $replaceMessage->render();
							}
							else
							{
								$replaceMessage = '';
							}
							
							foreach ($match as $matchEle)
							{
								$message['message'] = str_replace($matchEle, $replaceMessage, $message['message']);
							}
						}
					}
					break;
				default:
					break;
			}
		}
		
		return $totalMatchCount;
	}
}