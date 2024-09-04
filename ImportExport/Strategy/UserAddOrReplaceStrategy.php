<?php

namespace HexBrain\Bundle\UserImportBundle\ImportExport\Strategy;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\ChainEntityClassNameProvider;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\SecurityBundle\Owner\OwnerChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
    public function __construct(EventDispatcherInterface $eventDispatcher,
                                ImportStrategyHelper $strategyHelper,
                                FieldHelper $fieldHelper,
                                DatabaseHelper $databaseHelper,
                                ChainEntityClassNameProvider $chainEntityClassNameProvider,
                                TranslatorInterface $translator,
                                NewEntitiesHelper $newEntitiesHelper,
                                DoctrineHelper $doctrineHelper,
                                OwnerChecker $ownerChecker,
                                ConfigManager $configManager,
                                EncoderFactoryInterface $encoderFactory)
    {
        $this->configManager = $configManager;
        $this->encoderFactory = $encoderFactory;
        parent::__construct(
            $eventDispatcher,
            $strategyHelper,
            $fieldHelper,
            $databaseHelper,
            $chainEntityClassNameProvider,
            $translator,
            $newEntitiesHelper,
            $doctrineHelper,
            $ownerChecker
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $encoder = $this->encoderFactory->getEncoder($entity);

        $entity->setPassword($encoder->encodePassword($this->configManager->get('hex_brain_user_import.default_password'), $entity->getSalt()));
        $entity = parent::process($entity);

        $this->SendingEmailImportUsers($entity);

        return $entity;
    }

    public function SendingEmailImportUsers($entity)
    {
        $transport = (new \Swift_SmtpTransport(
            $this->configManager->get('oro_email.smtp_settings_host'),
            $this->configManager->get('oro_email.smtp_settings_port'))
        );

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('New Password'))
            ->setFrom([$this->configManager->get('oro_campaign.campaign_sender_email') => $this->configManager->get('oro_campaign.campaign_sender_name')])
            ->setTo([$entity->getEmail() => $entity->getFirstName()])
            ->setBody('You new password: '. '<b>'.$this->configManager->get('hex_brain_user_import.default_password') . '</b>');

        $mailer->send($message);
    }
}