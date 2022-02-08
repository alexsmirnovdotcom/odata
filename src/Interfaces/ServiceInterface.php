<?php

namespace Alexsmirnovdotcom\Odata\Interfaces;

use GuzzleHttp\Client;

/**
 * Библиотека для работы с 1С по протоколу ODATA.
 *
 * Class OData
 */
interface ServiceInterface
{
    /**
     * @param ConfigInterface|null $config
     * @param Client|null $client
     * @param QueryParametersInterface|null $queryParameters
     * @param RequestExceptionHandlerInterface|null $exceptionHandler
     */
    public function __construct(
        ConfigInterface           $config = null,
        Client                    $client = null,
        QueryParametersInterface  $queryParameters = null,
        RequestExceptionHandlerInterface $exceptionHandler = null
    );

    /**
     * @param array $auth
     * @return $this
     */
    public function authAs(array $auth): ServiceInterface;

    /**
     * Устанавливает имя ресурса к которому идет запрос.
     * Например Document_СчетНаОплатуПокупателю
     *
     * @param string $resource
     * @return $this
     */
    public function resource(string $resource): ServiceInterface;

    /**
     * Алиас для метода resource().
     *
     * @param string $resource
     * @return $this
     * @see resource().
     */
    public function from(string $resource): ServiceInterface;

    /**
     * Выполняется GET запрос только с параметром $filter, и возвращает количество записей.
     * Все остальные параметры игнорируются.
     *
     * @return ResponseInterface
     */
    public function getOnlyCount(): ResponseInterface;

    /**
     * Делает запрос к 1с, и возвращает результат.
     * В случае ошибки выбрасывается исключение.
     * Для получения конкретной сущности нужно передать его GUID в параметры метода.
     * Значение GUID валидируется и выбрасывается исключение при ошибке.
     *
     * @param string $guid
     * @return ResponseInterface
     */
    public function get(string $guid = ''): ResponseInterface;

    /**
     * Алиас для top()
     *
     * @param int $value
     * @return $this
     * @see top()
     */
    public function limit(int $value): ServiceInterface;

    /**
     * Устанавливает значение параметра $format=application/json;odata=nometadata.
     * В ответе будут отсутствовать любые метаданные.
     *
     * @return $this
     */
    public function noMetadata(): ServiceInterface;

    /**
     * Устанавливает параметр "inlinecount" в значение "allpages".
     * При использовании этого параметра в результате запроса вернется и количество найденных записей.
     * Вернется общее количество записей, независимо от значений параметров $skip и $top.
     * При использовании вместе с параметром $expand на тестовой базе возвращало ошибку.
     *
     * @return $this
     */
    public function setInlineCountAllPages(): ServiceInterface;

    /**
     * Устанавливает значение параметра $top,
     * который ограничивает количество записей в ответе.
     * Все значения <= 0 будут установлены в 0.
     *
     * @param int $value
     * @return $this
     */
    public function top(int $value): ServiceInterface;

    /**
     * Устанавливает значение параметра $skip,
     * который указывает сколько записей нужно пропустить.
     * При построении пагинации рекомендую пользоваться сортировкой,
     * т.к. без нее база ведет себя некорректно и пропускает записи.
     * Все значения <= 0 будут установлены в 0.
     * При этом размер выборки не будет ограничен и вернутся все записи из базы.
     *
     * @param int $value
     * @return $this
     */
    public function skip(int $value): ServiceInterface;

    /**
     * Возвращает конфигурацию в виде массива.
     * @return array
     */
    public function debug(): array;

    /**
     * Устанавливает значение параметра $select для отбора только нужных полей.
     * Поля необходимо передавать в виде массива по одному, тогда строка будет сформирована автоматически
     * или сразу готовой строкой (записи должны быть разделены запятой).
     *
     * @param array|string $select
     * @return $this
     */
    public function select($select): ServiceInterface;

    /**
     * Отправляет POST запрос в 1С для создания записи
     * с параметрами переданными в массиве $data[].
     * Если в массиве есть запись в Ref_Key с валидным Guid, запись создастся с ним.
     * (Если запись с таким Ref_Key уже есть, будет ошибка с кодом 500).
     *
     * Все данные должны быть типов string|numeric|bool|array.
     * Остальные будут удалены перед выполнением запроса.
     *
     * @param array $data
     * @return ResponseInterface
     */
    public function create(array $data = []): ResponseInterface;

    /**
     * Отправляет PATCH запрос на изменение данных
     * в сущности с $guid, и параметрами $data[].
     *
     * Все данные должны быть типов string|numeric|bool|array.
     * Остальные будут удалены перед выполнением запроса.
     *
     * @param string $guid
     * @param array $data
     * @return ResponseInterface
     */
    public function update(string $guid, array $data): ResponseInterface;

    /**
     * Отправляет PATCH запрос на установку пометки на удаление,
     * и возвращает результат.
     * По умолчанию это поле DeletionMark, но его можно изменить передав вторым параметром
     * $deletionMarkKey его название.
     *
     * Если в $deletionMarkKey передана пустая строка, будет установлено значение по умолчанию.
     *
     * @param string $guid
     * @param string $deletionMarkKey
     * @return ResponseInterface
     */
    public function markDeleted(string $guid, string $deletionMarkKey = 'DeletionMark'): ResponseInterface;

    /**
     * ВНИМАНИЕ! 1С не производит никаких проверок на ссылочную целостность для возможности удаления
     * и удаляет запись незамедлительно! Будьте осторожны!
     * Это приведет к существенным ошибкам в данных при неверном использовании!
     * Вместо этого рекомендуется использовать метод markDeleted() для
     * установки пометки на удаление.
     *
     * Отправляет DELETE запрос на удаление записи с переданным guid.
     *
     * @param string $guid
     * @return ResponseInterface
     * @see markDeleted()
     */
    public function forceDelete(string $guid): ResponseInterface;

    /**
     * Устанавливает значение параметра $filter для фильтрации запроса.
     * Необходимо передать уже готовую строку.
     * В планах реализовать небольшой QueryBuilder.
     *
     * @param string $filter
     * @return $this
     */
    public function filter(string $filter): ServiceInterface;

    /**
     * Устанавливает параметр $orderby для сортировки записей.
     * Необходимо передать массив с полями и направлениями сортировки или уже готовую строку.
     * Например: ['Date desc', 'СуммаДокумента asc'] или сразу в виде строки: "Date desc, СуммаДокумента asc".
     * Значения передаются как есть, никаких проверок на валидность направления сортировки не производится.
     *
     * @param array|string $orders
     * @return $this
     */
    public function orderBy($orders): ServiceInterface;

    /**
     * Устанавливает значение параметра $expand для одновременного подзапроса по указанным сущностям.
     * Поля необходимо передавать в виде массива по одному, тогда строка будет сформирована автоматически
     * или сразу готовой строкой. Записи должны быть разделены запятой.
     *
     * Например: $odata->select(['Ref_Key', 'Организация/Ref_Key/Description'])->expand(['Организация']).
     * $expand нельзя использовать при запросе одиночных сущностей.
     *
     * @param array|string $expand
     * @return $this
     */
    public function expand($expand): ServiceInterface;

    /**
     * Устанавливает URI базы данных.
     *
     * @param string $host
     * @return $this
     */
    public function setHost(string $host): ServiceInterface;

    /**
     * Дополнительные параметры для Guzzle Client. (например timeout и т.д.)
     * Должны передаваться в виде массива [key => value,].
     * @param array $params
     * @return ServiceInterface
     */
    public function setAdditionalClientParams(array $params): ServiceInterface;

    /**
     * Добавляет/изменяет заголовок $header со значением $value.
     *
     * @param string $header Название заголовка (напр. Accept).
     * @param string $value Значение заголовка (напр. application/json).
     * @return ServiceInterface
     */
    public function setHeader(string $header, string $value): ServiceInterface;

    /**
     * Удаляет значение заголовка $header из Headers.
     * Выполняется обычный unset, никаких проверок на наличие не производится.
     *
     * @param string $header Название заголовка.
     * @return ServiceInterface
     */
    public function clearHeader(string $header): ServiceInterface;

    /**
     * Устанавливает ваш собственный обработчик исключений для запросов.
     * Обработчик должен имплементировать \Alexsmirnovdotcom\Odata\Interfaces\RequestExceptionHandlerInterface
     *
     * @see \Alexsmirnovdotcom\Odata\Interfaces\RequestExceptionHandlerInterface
     * @param RequestExceptionHandlerInterface $handler Экземпляр класса обработчика исключений.
     * @return ServiceInterface
     */
    public function setRequestExceptionHandler(RequestExceptionHandlerInterface $handler): ServiceInterface;
}
