#Root user check
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 
   exit 1
fi

echo "Please enter the Google Distance Matrix API Key:"
read api_key

# Download and Install the Latest Updates for the OS
apt-get install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update && apt-get upgrade -y

#Install php7.2
apt install -y php7.2-fpm php7.2-common php7.2-mbstring php7.2-xmlrpc php7.2-soap php7.2-gd php7.2-xml php7.2-intl php7.2-mysql php7.2-cli php7.2-zip php7.2-curl

# Install essential packages
apt-get -y install zsh htop

# Install MySQL Server in a Non-Interactive mode. Default root password will be "root"
echo "mysql-server-5.7 mysql-server/root_password password root" | debconf-set-selections
echo "mysql-server-5.7 mysql-server/root_password_again password root" | debconf-set-selections
apt-get -y install mysql-server-5.7


apt -y install expect

SECURE_MYSQL=$(expect -c "

set timeout 10
spawn mysql_secure_installation

expect \"Enter password for user root:\"
send \"root\r\"


expect \"VALIDATE PASSWORD PLUGIN can be used to test passwords
and improve security. It checks the strength of password
and allows the users to set only those passwords which are
secure enough. Would you like to setup VALIDATE PASSWORD plugin?

Press y|Y for Yes, any other key for No:\"
send \"n\r\"


expect \"Change the password for root ? ((Press y|Y for Yes, any other key for No) :\"
send \"n\r\"

expect \"Remove anonymous users? (Press y|Y for Yes, any other key for No) :"
send \"y\r\"

expect \"Disallow root login remotely? (Press y|Y for Yes, any other key for No) :"
send \"y\r\"

expect \"Remove test database and access to it? (Press y|Y for Yes, any other key for No) :"
send \"y\r\"

expect \"Reload privilege tables now? (Press y|Y for Yes, any other key for No) :"
send \"y\r\"

expect eof
")

echo "$SECURE_MYSQL"

apt -y purge expect

sed -i 's/127\.0\.0\.1/0\.0\.0\.0/g' /etc/mysql/my.cnf
mysql -uroot -proot -e 'USE mysql; UPDATE `user` SET `Host`="%" WHERE `User`="root" AND `Host`="localhost"; DELETE FROM `user` WHERE `Host` != "%" AND `User`="root"; FLUSH PRIVILEGES;'

service mysql restart
apt install composer -y

#install dependencies
composer install

#dump database schema
mysql -uroot -proot < database.sql

#Adjust app configuration
sed '18s/.*/GOOGLE_MAPS_KEY='$api_key'/' .env.example > .env

nohup php -S 0.0.0.0:8080 -t public 2>/dev/null &