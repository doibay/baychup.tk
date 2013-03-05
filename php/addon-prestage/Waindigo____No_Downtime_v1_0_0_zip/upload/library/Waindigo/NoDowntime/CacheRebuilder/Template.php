<?php

class Waindigo_NoDowntime_CacheRebuilder_Template extends XenForo_CacheRebuilder_Template
{
    /**
     * @see XenForo_CacheRebuilder_Template::rebuild()
     */
    public function rebuild($position = 0, array &$options = array(), &$detailedMessage = '')
    {
        $options = array_merge(array(
            'startStyle' => 0,
            'startTemplate' => 0,
            'maxExecution' => XenForo_Application::getConfig()->rebuildMaxExecution,
            'mapped' => false
        ), $options);

        /* @var $templateModel XenForo_Model_Template */
        $templateModel = XenForo_Model::create('XenForo_Model_Template');

        /* @var $optionModel XenForo_Model_Option */
        $optionModel = XenForo_Model::create('XenForo_Model_Option');

        if ($options['startStyle'] == 0 && $options['startTemplate'] == 0 && !$options['mapped']) {
    		$optionModel->updateOptions(array('templateFiles' => true));
        }

        $position = parent::rebuild($position, $options, $detailedMessage);

        if ($position === true) {
            $optionModel->updateOptions(array('templateFiles' => false));
        }

        return $position;
    } /* END Waindigo_NoDowntime_CacheRebuilder_Template::rebuild */
}