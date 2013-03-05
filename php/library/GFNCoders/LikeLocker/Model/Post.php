<?php //Nulled by VxF.cc
class GFNCoders_LikeLocker_Model_Post extends XFCP_GFNCoders_LikeLocker_Model_Post
{
	public function getQuoteTextForPost(array $post, $maxQuoteDepth = 0)
	{
		$message = parent::getQuoteTextForPost($post, $maxQuoteDepth);
		$message = preg_replace('/\[likelocker.*\].*\[\/likelocker\]/is', '', $message);
		
		return $message;
	}
}