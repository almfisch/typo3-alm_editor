<?php
use TYPO3\CMS\Backend\Controller;

return [
    'file_edit_rte' => [
        'path' => '/file/editcontentrte',
        'target' => Alm\AlmEditor\Controller\EditFileRteController::class . '::editAction'
    ],
];