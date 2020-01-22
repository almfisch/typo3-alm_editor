<?php
namespace Alm\AlmEditor\Hooks;

/**
 *
 * @hook TYPO3_CONF_VARS|SC_OPTIONS|fileList|editIconsHook
 */
class FileList implements \TYPO3\CMS\Filelist\FileListEditIconHookInterface
{
    /**
     *
     * @param array $cells Array of edit icons
     * @param \TYPO3\CMS\Filelist\FileList $parentObject Parent object
     */
    public function manipulateEditIcons(&$cells, &$parentObject)
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $fileTypes = $settings['plugin.']['tx_almeditor.']['settings.']['fileTypes'];

        $label = 'Edit File with WYSIWYG-Editor';

        $file = $cells['__fileOrFolderObject'];
        $fileType = '';

        if(property_exists($file, 'properties'))
        {
            $fileType = $file->getExtension();
        }

        if($file instanceof \TYPO3\CMS\Core\Resource\FileInterface && \TYPO3\CMS\Core\Utility\GeneralUtility::inList($fileTypes, strtolower($fileType)))
        {
            $iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);

            $fullIdentifier = $file->getCombinedIdentifier();
            $parameter = [
                'target' => $fullIdentifier
            ];
            
            $url = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
            $url = $url->buildUriFromRoute('file_edit_rte', $parameter);

            $editOnClick = 'top.list_frame.location.href=' . \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue($url) . '+\'&returnUrl=\'+top.rawurlencode(top.list_frame.document.location.pathname+top.list_frame.document.location.search);return false;';
            $edit2 = '<a href="#" class="btn btn-default btn_alm_editor" onclick="' . htmlspecialchars($editOnClick) . '" title="' . $label . '">'
                . $iconFactory->getIcon('actions-document-open', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render()
                . '</a>';

            $cells['edit'] = str_replace('class="btn btn-default"', 'class="btn btn-default btn_alm_editor_default"', $cells['edit']);

            $cells = array_merge(array('edit2' => $edit2), $cells);
        }
    }
}