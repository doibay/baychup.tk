<?php
class xenCODE_AutoPing_DataWriter extends XFCP_xenCODE_AutoPing_DataWriter {
	public function save() {
		parent::save();
		$options = XenForo_Application::get('options');
		$thread = $this->getMergedData();
		$subject = stripslashes($thread['title']);
		$subject = urlencode($subject);
		$url = $options['boardUrl'] . '/' . XenForo_Link::buildPublicLink('threads', $thread);
		$url = urlencode($url);
		$ping = 'http://pingomatic.com/ping/?title='.$subject.'&blogurl='.$url.'&chk_feedburner=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_postrank=on&chk_skygrid=on&chk_collecta=on&chk_rubhub=on';
		$ch = curl_init($ping);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);
		curl_close($ch);
	}
}