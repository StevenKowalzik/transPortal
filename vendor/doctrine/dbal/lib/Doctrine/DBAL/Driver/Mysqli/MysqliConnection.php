<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Driver\Mysqli;

use Doctrine\DBAL\Driver\Connection as Connection;
use \Doctrine\DBAL\Driver\PingableConnection;

/**
 * @author Kim Hemsø Rasmussen <kimhemsoe@gmail.com>
 * @author Till Klampaeckel <till@php.net>
 */
class MysqliConnection implements Connection, PingableConnection
{
    /**
     * @var \mysqli
     */
    private $_conn;

    /**
     * @param array  $params
     * @param string $username
     * @param string $password
     * @param array  $driverOptions
     *
     * @throws \Doctrine\DBAL\Driver\Mysqli\MysqliException
     */
    public function __construct(array $params, $username, $password, array $driverOptions = array())
    {
        $port = isset($params['port']) ? $params['port'] : ini_get('mysqli.default_port');
        $socket = isset($params['unix_socket']) ? $params['unix_socket'] : ini_get('mysqli.default_socket');

        $this->_conn = mysqli_init();

        $previousHandler = set_error_handler(function () {
        });

        if ( ! $this->_conn->real_connect($params['host'], $username, $password, $params['dbname'], $port, $socket)) {
            set_error_handler($previousHandler);

            throw new MysqliException($this->_conn->connect_error, $this->_conn->connect_errno);
        }

        set_error_handler($previousHandler);

        if (isset($params['charset'])) {
            $this->_conn->set_charset($params['charset']);
        }

        $this->setDriverOptions($driverOptions);
    }

    /**
     * Retrieves mysqli native resource handle.
     *
     * Could be used if part of your application is not using DBAL.
     *
     * @return \mysqli
     */
    public function getWrappedResourceHandle()
    {
        return $this->_conn;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($prepareString)
    {
        return new MysqliStatement($this->_conn, $prepareString);
    }

    /**
     * {@inheritdoc}
     */
    public function query()
    {
        $args = func_get_args();
        $sql = $args[0];
        $stmt = $this->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($input, $type=\PDO::PARAM_STR)
    {
        return "'". $this->_conn->escape_string($input) ."'";
    }

    /**
     * {@inheritdoc}
     */
    public function exec($statement)
    {
        $this->_conn->query($statement);
        return $this->_conn->affected_rows;
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        return $this->_conn->insert_id;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->_conn->query('START TRANSACTION');
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->_conn->commit();
    }

    /**
     * {@inheritdoc}non-PHPdoc)
     */
    public function rollBack()
    {
        return $this->_conn->rollback();
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
        return $this->_conn->errno;
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo()
    {
        return $this->_conn->error;
    }

    /**
     * Apply the driver options to the connection.
     *
     * @param array $driverOptions
     *
     * @throws MysqliException When one of of the options is not supported.
     * @throws MysqliException When applying doesn't work - e.g. due to incorrect value.
     */
    private function setDriverOptions(array $driverOptions = array())
    {
        $supportedDriverOptions = array(
            \MYSQLI_OPT_CONNECT_TIMEOUT,
            \MYSQLI_OPT_LOCAL_INFILE,
            \MYSQLI_INIT_COMMAND,
            \MYSQLI_READ_DEFAULT_FILE,
            \MYSQLI_READ_DEFAULT_GROUP,
        );

        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $supportedDriverOptions[] = \MYSQLI_SERVER_PUBLIC_KEY;
        }

        $exceptionMsg = "%s option '%s' with value '%s'";

        foreach ($driverOptions as $option => $value) {

            if (!in_array($option, $supportedDriverOptions, true)) {
                throw new MysqliException(
                    sprintf($exceptionMsg, 'Unsupported', $option, $value)
                );
            }

            if (@mysqli_options($this->_conn, $option, $value)) {
                continue;
            }

            $msg  = sprintf($exceptionMsg, 'Failed to set', $option, $value);
            $msg .= sprintf(', error: %s (%d)', mysqli_error($this->_conn), mysqli_errno($this->_conn));

            throw new MysqliException(
                $msg,
                mysqli_errno($this->_conn)
            );
        }
    }

    /**
     * Pings the server and re-connects when `mysqli.reconnect = 1`
     *
     * @return bool
     */
    public function ping()
    {
        return $this->_conn->ping();
    }
}
