<?php

/**
 * @file plugins/generic/reviewAndInProductionTabs/ReviewAndInProductionTabsPlugin.php
 *
 * Copyright (c) 2024 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class ReviewAndInProductionTabsPlugin
 * @ingroup plugins_generic_reviewAndInProductionTabs
 *
 * @brief Review And In Production Tabs plugin class
 */

namespace APP\plugins\generic\reviewAndInProductionTabs;

use PKP\plugins\GenericPlugin;
use APP\core\Application;
use PKP\plugins\Hook;

class ReviewAndInProductionTabsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
            Hook::add('TemplateManager::display', [$this, 'displayTabs']);
        }
        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.reviewAndInProductionTabsPlugin.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.reviewAndInProductionTabsPlugin.description');
    }

    public function displayTabs(string $hookName, array $params): bool
    {
        [$templateManager, $template] = $params;

        switch ($template) {
            case 'dashboard/index.tpl':
                $templateManager->registerFilter('output', [$this, 'tabsFilter']);
                break;
        }

        return Hook::CONTINUE;
    }

    public function tabsFilter($output, $templateMgr): string
    {
        if (!preg_match('/(<tab[^>]+id="archive"[^>]*>.*?<\/tab>)/s', $output, $matches, PREG_OFFSET_CAPTURE)) {
            return $output;
        }

        $offset = $matches[0][1] + strlen($matches[0][0]);

        $newOutput = substr($output, 0, $offset);
        $newOutput .= $templateMgr->fetch($this->getTemplateResource('tabs.tpl'));
        $newOutput .= substr($output, $offset);

        $output = $newOutput;

        return $output;
    }

    public function getCanEnable()
    {
        $request = Application::get()->getRequest();
        return $request->getContext() !== null;
    }

    public function getCanDisable()
    {
        $request = Application::get()->getRequest();
        return $request->getContext() !== null;
    }
}
