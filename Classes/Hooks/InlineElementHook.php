<?php
namespace Alm\AlmEditor\Hooks;

/**
 * Inline Element Hook
 */
class InlineElementHook implements \TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface
{
    protected $fileTypes;
    protected $editIrre;

    public function __construct()
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->fileTypes = $settings['plugin.']['tx_almeditor.']['settings.']['fileTypes'];
        $this->editIrre = $settings['plugin.']['tx_almeditor.']['settings.']['editIrre'];
        $this->editIrreRte = $settings['plugin.']['tx_almeditor.']['settings.']['editIrreRte'];
        $this->showIrreInfo = $settings['plugin.']['tx_almeditor.']['settings.']['showIrreInfo'];
    }



    /**
     * Pre-processing to define which control items are enabled or disabled.
     *
     * @param string $parentUid The uid of the parent (embedding) record (uid or NEW...)
     * @param string $foreignTable The table (foreign_table) we create control-icons for
     * @param array $childRecord The current record of that foreign_table
     * @param array $childConfig TCA configuration of the current field of the child record
     * @param bool $isVirtual Defines whether the current records is only virtually shown and not physically part of the parent record
     * @param array &$enabledControls (reference) Associative array with the enabled control items
     */
    public function renderForeignRecordHeaderControl_preProcess(
        $parentUid,
        $foreignTable,
        array $childRecord,
        array $childConfig,
        $isVirtual,
        array &$enabledControls
    )
    {
        if($this->showIrreInfo == '1')
        {
            $uid = $childRecord['uid_local'][0]['uid'];
            $file = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileObject($uid);
            $fileType = $file->getExtension();

            if(\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->fileTypes, strtolower($fileType)))
            {
                $enabledControls['info'] = true;
            }
        }
    }


    /**
     * Post-processing to define which control items to show. Possibly own icons can be added here.
     *
     * @param string $parentUid The uid of the parent (embedding) record (uid or NEW...)
     * @param string $foreignTable The table (foreign_table) we create control-icons for
     * @param array $childRecord The current record of that foreign_table
     * @param array $childConfig TCA configuration of the current field of the child record
     * @param bool $isVirtual Defines whether the current records is only virtually shown and not physically part of the parent record
     * @param array &$controlItems (reference) Associative array with the currently available control items
     */
    public function renderForeignRecordHeaderControl_postProcess(
        $parentUid,
        $foreignTable,
        array $childRecord,
        array $childConfig,
        $isVirtual,
        array &$controlItems
    )
    {
        if($this->editIrre == '1' || $this->editIrreRte == '1')
        {
            $uid = $childRecord['uid_local'][0]['uid'];
            $file = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileObject($uid);
            $fileType = $file->getExtension();

            if(\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->fileTypes, strtolower($fileType)))
            {
                $label1 = 'Edit File with WYSIWYG-Editor';
                $label2 = 'Edit File';

                $edit1 = '';
                $edit2 = '';

                $iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
                
                $fullIdentifier = $file->getCombinedIdentifier();
                //$returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::sanitizeLocalUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('returnUrl'));
                $returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::sanitizeLocalUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'));
                $parameter = [
                    'target' => $fullIdentifier,
                    'returnUrl' => $returnUrl
                ];

                $uriBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);

                if($this->editIrreRte == '1')
                {
                    $url = $uriBuilder->buildUriFromRoute('file_edit_rte', $parameter);
                    $edit1 = '<a href="' . $url . '" class="btn btn-default btn_alm_editor" title="' . $label1 . '">'
                        . $iconFactory->getIcon('actions-document-open', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render()
                        . '</a>';
                }

                if($this->editIrre == '1')
                {
                    $url = $uriBuilder->buildUriFromRoute('file_edit', $parameter);
                    $edit2 = '<a href="' . $url . '" target="_blank" class="btn btn-default btn_alm_editor_default" title="' . $label2 . '">'
                        . $iconFactory->getIcon('actions-document-open', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render()
                        . '</a>';
                }
                
                $controlItems['edit'] = $edit1 . $edit2 . $controlItems['edit'];
            }
        }
    }
}