<?php

namespace HexBrain\Bundle\UserImportBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    protected $configManager;
    protected $encoderFactory;

    /**
     * @param ImportStrategyHelper $helper
     * @param FieldHelper $fieldHelper
     * @param ConfigManager $configManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(ImportStrategyHelper $helper, FieldHelper $fieldHelper, ConfigManager $configManager, EncoderFactoryInterface $encoderFactory)
    {
        $this->strategyHelper = $helper;
        $this->fieldHelper = $fieldHelper;
        $this->configManager = $configManager;
        $this->encoderFactory = $encoderFactory;
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