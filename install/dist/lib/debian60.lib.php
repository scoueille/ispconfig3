<?php

/*
Copyright (c) 2007, Till Brehm, projektfarm Gmbh
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class installer extends installer_base {
	
	public function configure_dovecot()
    {
		global $conf;
		
		$config_dir = $conf['dovecot']['config_dir'];
		
		//* Configure master.cf and add a line for deliver
		if(is_file($config_dir.'/master.cf')){
            copy($config_dir.'/master.cf', $config_dir.'/master.cf~2');
        }
		if(is_file($config_dir.'/master.cf~')){
            exec('chmod 400 '.$config_dir.'/master.cf~2');
        }
		$content = rf($conf["postfix"]["config_dir"].'/master.cf');
		// Only add the content if we had not addded it before
		if(!stristr($content,"dovecot/deliver")) {
			$deliver_content = 'dovecot   unix  -       n       n       -       -       pipe'."\n".'  flags=DRhu user=vmail:vmail argv=/usr/lib/dovecot/deliver -f ${sender} -d ${user}@${nexthop}';
			af($conf["postfix"]["config_dir"].'/master.cf',$deliver_content);
		}
		unset($content);
		unset($deliver_content);
		
		
		//* Reconfigure postfix to use dovecot authentication
		// Adding the amavisd commands to the postfix configuration
		$postconf_commands = array (
			'dovecot_destination_recipient_limit = 1',
			'virtual_transport = dovecot',
			'smtpd_sasl_type = dovecot',
			'smtpd_sasl_path = private/auth'
		);
		
		// Make a backup copy of the main.cf file
		copy($conf["postfix"]["config_dir"].'/main.cf',$conf["postfix"]["config_dir"].'/main.cf~3');
		
		// Executing the postconf commands
		foreach($postconf_commands as $cmd) {
			$command = "postconf -e '$cmd'";
			caselog($command." &> /dev/null", __FILE__, __LINE__, "EXECUTED: $command", "Failed to execute the command $command");
		}
		
		//* copy dovecot.conf
		$configfile = 'dovecot.conf';
		if(is_file("$config_dir/$configfile")){
            copy("$config_dir/$configfile", "$config_dir/$configfile~");
        }
		copy('tpl/debian_dovecot.conf.master',"$config_dir/$configfile");
		
		//* dovecot-sql.conf
		$configfile = 'dovecot-sql.conf';
		if(is_file("$config_dir/$configfile")){
            copy("$config_dir/$configfile", "$config_dir/$configfile~");
        }
		exec("chmod 400 $config_dir/$configfile~");
		$content = rf("tpl/debian6_dovecot-sql.conf.master");
		$content = str_replace('{mysql_server_ispconfig_user}',$conf['mysql']['ispconfig_user'],$content);
		$content = str_replace('{mysql_server_ispconfig_password}',$conf['mysql']['ispconfig_password'], $content);
		$content = str_replace('{mysql_server_database}',$conf['mysql']['database'],$content);
		$content = str_replace('{mysql_server_host}',$conf['mysql']['host'],$content);
		wf("$config_dir/$configfile", $content);
		
		exec("chmod 600 $config_dir/$configfile");
		exec("chown root:root $config_dir/$configfile");

	}

}

?>