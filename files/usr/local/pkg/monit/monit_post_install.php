<?php

 /* monit_post_install.php
 *
 * Copyright (C) 2006 Scott Ullrich
 * Copyright (C) 2009-2010 Robert Zelaya
 * Copyright (C) 2011-2012 Ermal Luci
 * Copyright (C) 2013 Bill Meeks
 * part of pfSense
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/****************************************************************************/
/* This module is called once during the monit package installation to   */
/* perform required post-installation setup.  It should only be executed    */
/* from the Package Manager process via the custom-post-install hook in     */
/* the monit.xml package configuration file.                             */
/****************************************************************************/

require_once("config.inc");
require_once("functions.inc");
require_once("/usr/local/pkg/monit/monit.inc");

global $config, $g;

/* Update monit package version in configuration */
$config['installedpackages']['monitglobal']['monit_config_ver'] = "1.0";

if(!isset($config['installedpackages']['monitglobal']['monit_config']))
$config['installedpackages']['monitglobal']['monit_config'] = "c2V0IGRhZW1vbiAgNjANCnNldCBodHRwZCBwb3J0IDI4MTIgYW5kDQogICAgYWxsb3cgYWRtaW46bW9uaXQNCmNoZWNrIHByb2Nlc3MgbW9uaXQgd2l0aCBwaWRmaWxlIC92YXIvcnVuL21vbml0LnBpZA==";

/* Update General Settings with default values if not already set */
log_error("[monit] killing any monit processes");
exec('/usr/bin/killall -9 monit 2>/dev/null');

monit_write_rcfile();
monit_write_config();
monit_write_shortcutfile();

log_error("[monit] monit.sh start: 6");
exec('/usr/local/bin/monit -c /usr/local/pkg/monit/monitrc');

/* Done with post-install, so clear flag */
log_error(gettext("[monit] Package post-installation tasks completed..."));
return true;

?>
