<?php
function get_bash_value($prompt)
{
    $temp_file = __DIR__.'/../../temp-data-'.md5(date('Y-m-d H:i:s')).'txt';
    file_put_contents($temp_file, '');
    echo $prompt;
    shell_exec(sprintf('read vname; echo $vname > %s;', $temp_file));
    $value = trim(file_get_contents($temp_file));
    unlink($temp_file);
    return $value;
}

if(file_exists(__DIR__.'/../.htpasswd')) {
    echo 'There is already an htpasswd file. Please remove it and try again.';
    exit;
}
$htaccess_file_contents = file_get_contents(__DIR__.'/../html/.htaccess');
if(preg_match('/AuthType/', $htaccess_file_contents)) {
    echo 'There is already something in your .htaccess file indicating that you are using a password. Please remove and try again.';
    exit;
}
if(file_exists(__DIR__.'/../.htpasswd')) {
    echo 'There is already an htpasswd file. Please remove it and try again.';
    exit;
}

echo "These settings will cause an authentication box to appear if a visitor arrives at this domain. \r\n";

if('Y' == get_bash_value('Do you want to password protect this domain? Y/n: ')) {
    $authuser = get_bash_value('Please enter a username. This will be used on all environments if applicable. Username: ');
    $clear_text_htpassword = get_bash_value('Please enter a password. This will be used on all environments if applicable. Password: ');
    $password = crypt($clear_text_htpassword, base64_encode($clear_text_htpassword));
    $string = $authuser.':'.$password;
    file_put_contents(__DIR__.'/../.htpasswd', $string);

    // replace contents in .htaccess
    $htaccess_contents = file_get_contents(__DIR__.'/../html/.htaccess');
    $template_contents = file_get_contents(__DIR__.'/htpass-template');
    $template_contents = str_replace('{{htpassfile}}', realpath(__DIR__.'/../.htpasswd'), $template_contents);
    $new_htaccess_contents = $template_contents."\r\n".$htaccess_contents;
    file_put_contents(__DIR__.'/../html/.htaccess', $new_htaccess_contents);

    echo "\r\n ...htpasswd authentication settings completed \r\n";
    exit;
}
