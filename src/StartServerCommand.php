<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use EcomDev\CrossDomainSessionManager\Console\OptionsMapper;
use EcomDev\CrossDomainSessionManager\Console\ServerOptionDefinitionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartServerCommand extends Command
{
    /**
     * @var ServerSettingsFactory
     */
    private $settingsFactory;
    /**
     * @var HttpServerFactory
     */
    private $serverFactory;
    /**
     * @var OptionsMapper
     */
    private $optionsMapper;

    public function __construct(
        string $name,
        ServerSettingsFactory $settingsFactory = null,
        HttpServerFactory $serverFactory = null,
        OptionsMapper $optionsMapper = null
    ) {
        $this->setName($name);
        $this->setDefinition(ServerOptionDefinitionFactory::createDefinition());

        $this->settingsFactory = $settingsFactory ?? new ServerSettingsFactory();
        $this->serverFactory = $serverFactory ?? new HttpServerFactory();
        $this->optionsMapper = $optionsMapper ?? new OptionsMapper();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $this->serverFactory->createServer();
        $data = $this->optionsMapper->mapConsoleInput($input);
        var_dump($data);
        $server->run($this->settingsFactory->createFromArray($data));
    }


}
