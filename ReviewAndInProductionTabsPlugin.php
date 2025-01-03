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

class ReviewAndInProductionTabsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
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
