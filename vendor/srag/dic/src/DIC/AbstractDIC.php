<?php

namespace srag\DIC\OpenCast\DIC;

use ILIAS\DI\Container;
use srag\DIC\OpenCast\Database\DatabaseDetector;
use srag\DIC\OpenCast\Database\DatabaseInterface;

/**
 * Class AbstractDIC
 *
 * @package srag\DIC\OpenCast\DIC
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDIC implements DICInterface
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container &$dic)
    {
        $this->dic = &$dic;
    }


    /**
     * @inheritDoc
     */
    public function database() : DatabaseInterface
    {
        return DatabaseDetector::getInstance($this->databaseCore());
    }
}
