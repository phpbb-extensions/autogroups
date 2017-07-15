#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

EPV=$1
NOTESTS=$2

if [ "$EPV" == "1" -a "$NOTESTS" == "1" ]
then
	cd phpBB
	composer require phpbb/epv:dev-master --ignore-platform-reqs --dev --no-interaction
	cd ../
fi
