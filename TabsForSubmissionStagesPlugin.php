<?php

/**
 * @file plugins/generic/tabsForSubmissionStages/TabsForSubmissionStagesPlugin.php
 *
 * Copyright (c) 2025 Lepidus Tecnologia
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class TabsForSubmissionStagesPlugin
 * @ingroup plugins_generic_tabsForSubmissionStages
 *
 * @brief Tabs for Submission Stages plugin class
 */

namespace APP\plugins\generic\tabsForSubmissionStages;

use PKP\plugins\GenericPlugin;
use APP\core\Application;
use PKP\plugins\Hook;
use PKP\db\DAORegistry;
use PKP\security\Role;
use PKP\template\PKPTemplateManager;
use PKP\submission\PKPSubmission;

class TabsForSubmissionStagesPlugin extends GenericPlugin
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
        return __('plugins.generic.tabsForSubmissionStages.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.tabsForSubmissionStages.description');
    }

    public function displayTabs(string $hookName, array $params): bool
    {
        [$templateManager, $template] = $params;

        if ($template !== 'dashboard/index.tpl') {
            return false;
        }

        $userRoles = $templateManager->getTemplateVars('userRoles');

        if (!array_intersect([Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER], $userRoles)) {
            return false;
        }

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $dispatcher = $request->getDispatcher();
        $apiUrl = $dispatcher->url($request, Application::ROUTE_API, $context->getPath(), '_submissions');

        $componentsState = $templateManager->getState('components');

        $this->loadResources($request, $templateManager);

        $inReviewListPanel = new \APP\components\listPanels\SubmissionsListPanel(
            'customSubmissions',
            __('common.queue.short.submissionsInReview'),
            [
                'apiUrl' => $apiUrl,
                'getParams' => [
                    'stageIds' => [WORKFLOW_STAGE_ID_INTERNAL_REVIEW, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW],
                    'status' => [PKPSubmission::STATUS_QUEUED]
                ],
                'lazyLoad' => true,
                'includeIssuesFilter' => $includeIssuesFilter,
                'includeAssignedEditorsFilter' => $includeAssignedEditorsFilter,
                'includeActiveSectionFiltersOnly' => true,
            ]
        );
        $componentsState[$inReviewListPanel->id] = $inReviewListPanel->getConfig();

        $inProductionListPanel = new \APP\components\listPanels\SubmissionsListPanel(
            'inProduction',
            __('plugins.generic.tabsForSubmissionStages.acceptedOrInProductionTabLabel'),
            [
                'apiUrl' => $apiUrl,
                'getParams' => [
                    'stageIds' => [WORKFLOW_STAGE_ID_EDITING, WORKFLOW_STAGE_ID_PRODUCTION],
                    'status' => [PKPSubmission::STATUS_QUEUED]
                ],
                'lazyLoad' => true,
                'includeIssuesFilter' => $includeIssuesFilter,
                'includeAssignedEditorsFilter' => $includeAssignedEditorsFilter,
                'includeActiveSectionFiltersOnly' => true,
            ]
        );
        $componentsState[$inProductionListPanel->id] = $inProductionListPanel->getConfig();

        $templateManager->setState(['components' => $componentsState]);

        $templateManager->registerFilter('output', [$this, 'tabsFilter']);

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

    private function loadResources($request, $templateMgr)
    {
        $pluginFullPath = $request->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath();

        $templateMgr->addJavaScript(
            'custom-submissions-list-item',
            $pluginFullPath . '/js/components/CustomSubmissionsListItem.js',
            [
                'priority' => PKPTemplateManager::STYLE_SEQUENCE_LAST,
                'contexts' => ['backend']
            ]
        );

        $templateMgr->addStyleSheet(
            'custom-submissions-list-style',
            $pluginFullPath . '/styles/submissionsPage.css',
            [
                'priority' => PKPTemplateManager::STYLE_SEQUENCE_LAST,
                'contexts' => ['backend']
            ]
        );
    }
}
