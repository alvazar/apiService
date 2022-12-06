<?php

namespace App\Repository;

use App\Core\Repository;
use App\Core\Storage\MysqlPdo;

class DBRepository extends Repository
{
    protected const ERRORS = [
        'connection' => 'Не удалось подключиться к базе данных',
        'tableNotFound' => 'Не задана таблица базы данных',
    ];

    protected $db;

    public function __construct()
    {
        $this->db = MysqlPdo::getInstance();

        if (!isset($this->db)) {
            $this->setError(self::ERRORS['connection']);
        }
    }

    public function get()
    {
        $table = $this->getParam('table');
        $where = $this->getParam('where');
        $orderBy = $this->getParam('orderBy');

        if (!isset($table)) {
            $this->setError(self::ERRORS['tableNotFound']);

            return;
        }

        $qu = sprintf('SELECT * FROM %s', $table);
        $values = [];

        if (isset($where)) {
            $qu .= ' WHERE ';

            foreach ($where as $key => $value) {
                $qu .= sprintf("%s = ? AND ", $key);
                $values[] = $value;
            }

            $qu = mb_substr($qu, 0, -5);
        }

        if (isset($orderBy)) {
            $qu .= sprintf(' ORDER BY %s', $orderBy);
        }

        $stmt = $this->db->prepare($qu);

        $stmt->execute($values);

        $items = [];

        while($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $items[] = $item;
        }

        return $items;
    }

    public function send(array $params = []): void
    {
        $table = $this->getParam('table');
        $fields = $this->getParam('fields');
        $where = $this->getParam('where');

        if (!isset($table)) {
            $this->setError(self::ERRORS['tableNotFound']);

            return;
        }

        if (isset($where)) {
            $currItem = $this->get()[0] ?? [];
        }

        $setFields = '';
        $insertFields = '';
        $values = [];

        foreach ($fields as $key => $value) {
            $setFields .= sprintf("%s = ?, ", $key);
            $insertFields .= $key . ', ';
            $values[] = $value;
        }

        $setFields = mb_substr($setFields, 0, -2);
        $insertFields = mb_substr($insertFields, 0, -2);

        if (!empty($currItem)) {
            $qu = sprintf(
                'UPDATE %s SET %s WHERE ID = %d LIMIT 1',
                $table,
                $setFields,
                $currItem['ID']
            );
        } else {
            $qu = sprintf(
                'INSERT INTO %s (%s) VALUES(%s)',
                $table,
                $insertFields,
                mb_substr(str_repeat('?,', count($values)), 0, -1)
            );
        }

        $stmt = $this->db->prepare($qu);

        $stmt->execute($values);
    }
}
