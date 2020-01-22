<?php
namespace Alm\AlmEditor\Controller;

class EditFileRteController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Module content accumulated.
     *
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $title;

    /**
     * Document template object
     *
     * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public $doc;

    /**
     * Original input target
     *
     * @var string
     */
    public $origTarget;

    /**
     * The original target, but validated.
     *
     * @var string
     */
    public $target;

    /**
     * Return URL of list module.
     *
     * @var string
     */
    public $returnUrl;

    /**
     * the file that is being edited on
     *
     * @var \TYPO3\CMS\Core\Resource\AbstractFile
     */
    protected $fileObject;

    /**
     *
     * @var \TYPO3\CMS\Backend\Template\ModuleTemplate
     */
    protected $moduleTemplate;




    public function __construct()
    {
        $this->init();
    }




    public function init()
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->fileTypes = $settings['plugin.']['tx_almeditor.']['settings.']['fileTypes'];

        $this->target = ($this->origTarget = ($fileIdentifier = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('target')));
        $this->returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::sanitizeLocalUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('returnUrl'));

        $this->moduleTemplate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\ModuleTemplate::class);

        $pageRenderer = $this->moduleTemplate->getPageRenderer();
        $pageRenderer->addJsFile('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor.js');
        $pageRenderer->addJsFile('EXT:alm_editor/Resources/Public/Backend/JavaScript/alm_editor.js');

        if($fileIdentifier)
        {
            $this->fileObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()
                ->retrieveFileOrFolderObject($fileIdentifier);
        }

        if(!$this->fileObject)
        {
            $title = $this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_file_list.xlf:paramError');
            $message = $this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_file_list.xlf:targetNoDir');
            throw new \RuntimeException($title . ': ' . $message, 1294586841);
        }
        if($this->fileObject->getStorage()->getUid() === 0)
        {
            throw new InsufficientFileAccessPermissionsException(
                'You are not allowed to access files outside your storages',
                1375889832
            );
        }

        $icon = $this->moduleTemplate->getIconFactory()->getIcon('apps-filetree-root', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render();
        $this->title = $icon
            . htmlspecialchars(
                $this->fileObject->getStorage()->getName()
            ) . ': ' . htmlspecialchars(
                $this->fileObject->getIdentifier()
            );

        $this->moduleTemplate->addJavaScriptCode(
            'FileEditBackToList',
            'function backToList() {
                top.goToModule("file_FilelistList");
            }'
        );
    }




    public function editAction(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $this->getButtons();

        $assigns = [];
        $assigns['moduleUrlTceFile'] = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('tce_file');
        $assigns['fileName'] = $this->fileObject->getName();

        $extList = $this->fileTypes;
        try {
            if (!$extList || !\TYPO3\CMS\Core\Utility\GeneralUtility::inList($extList, $this->fileObject->getExtension())) {
                throw new \Exception('Files with that extension are not editable.', 1476050135);
            }

            // Read file content to edit:
            $fileContent = $this->fileObject->getContents();

            // Making the formfields
            $hValue = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('file_edit_rte', [
                'target' => $this->origTarget,
                'returnUrl' => $this->returnUrl
            ]);
            $assigns['uid'] = $this->fileObject->getUid();
            $assigns['fileContent'] = $fileContent;
            $assigns['hValue'] = $hValue;
        } catch (\Exception $e) {
            $assigns['extList'] = $extList;
        }

        // Rendering of the output via fluid
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setLayoutRootPaths([\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:alm_editor/Resources/Private/Layouts')]);
        $view->setTemplateRootPaths([\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:alm_editor/Resources/Private/Templates')]);
        $view->setPartialRootPaths([\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:alm_editor/Resources/Private/Partials')]);
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:alm_editor/Resources/Private/Templates/EditFile.html'));
        $view->assignMultiple($assigns);
        $pageContent = $view->render();

        $this->content .= $pageContent;
        $this->moduleTemplate->setContent($this->content);

        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }




    public function getButtons()
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $lang = $this->getLanguageService();
        // CSH button
        $helpButton = $buttonBar->makeHelpButton()
            ->setFieldName('file_edit')
            ->setModuleName('xMOD_csh_corebe');
        $buttonBar->addButton($helpButton);

        // Save button
        $saveButton = $buttonBar->makeInputButton()
            ->setName('_save')
            ->setValue('1')
            ->setOnClick('document.editform.submit();')
            ->setTitle($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:file_edit.php.submit'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-save', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL));

        // Save and Close button
        $saveAndCloseButton = $buttonBar->makeInputButton()
            ->setName('_saveandclose')
            ->setValue('1')
            ->setOnClick(
                'document.editform.redirect.value='
                . \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue($this->returnUrl)
                . '; document.editform.submit();'
            )
            ->setTitle($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:file_edit.php.saveAndClose'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon(
                'actions-document-save-close',
                \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
            ));

        $splitButton = $buttonBar->makeSplitButton()
            ->addItem($saveButton)
            ->addItem($saveAndCloseButton);
        $buttonBar->addButton($splitButton, \TYPO3\CMS\Backend\Template\Components\ButtonBar::BUTTON_POSITION_LEFT, 20);

        // Cancel button
        $closeButton = $buttonBar->makeLinkButton()
            ->setHref($this->returnUrl)
            ->setTitle($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.cancel'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-close', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL));
        $buttonBar->addButton($closeButton, \TYPO3\CMS\Backend\Template\Components\ButtonBar::BUTTON_POSITION_LEFT, 10);

        // Make shortcut:
        $shortButton = $buttonBar->makeShortcutButton()
            ->setModuleName('file_edit')
            ->setGetVariables(['target']);
        $buttonBar->addButton($shortButton);
    }




    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }




    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}