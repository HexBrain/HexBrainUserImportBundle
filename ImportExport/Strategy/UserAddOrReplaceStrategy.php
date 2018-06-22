<?php

namespace HexBrain\Bundle\UserImportBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\EntityBundle\Provider\ChainEntityClassNameProvider;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Owner\OwnerChecker;
class UserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    protected $configManager;
    protected $encoderFactory;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param ImportStrategyHelper $helper
     * @param FieldHelper $fieldHelper
     * @param DatabaseHelper $databaseHelper
     * @param ChainEntityClassNameProvider $chainEntityClassNameProvider
     * @param TranslatorInterface $translator
     * @param NewEntitiesHelper $newEntitiesHelper
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager $configManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ImportStrategyHelper $helper,
        FieldHelper $fieldHelper,
        DatabaseHelper $databaseHelper,
        ChainEntityClassNameProvider $chainEntityClassNameProvider,
        TranslatorInterface $translator,
        NewEntitiesHelper $newEntitiesHelper,
        DoctrineHelper $doctrineHelper,
        OwnerChecker $ownerChecker,
        ConfigManager $configManager,
        EncoderFactoryInterface $encoderFactory
    ){
        parent::__construct(
            $eventDispatcher,
            $helper,
            $fieldHelper,
            $databaseHelper,
            $chainEntityClassNameProvider,
            $translator,
            $newEntitiesHelper,
            $doctrineHelper,
            $ownerChecker
        );
        $this->encoderFactory = $encoderFactory;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $encoder = $this->encoderFactory->getEncoder($entity);
        $entity->setPassword($encoder->encodePassword($this->configManager->get('hex_brain_user_import.default_password'), $entity->getSalt()));
        $entity = parent::process($entity);

        return $entity;
    }
}