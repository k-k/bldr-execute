<?php

/**
 * This file is part of Bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Extension\Execute\Call;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aaron Scherer <aaron@undergroundelephant.com>
 */
class ExecuteCall implements \Bldr\Call\CallInterface
{
    /**
     * {@inheritDoc}
     */
    public function run(OutputInterface $output, array $arguments)
    {
        $output->writeln(["", print_r($arguments, true), ""]);
    }
}
