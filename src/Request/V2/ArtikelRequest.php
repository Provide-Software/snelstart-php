<?php
/**
 * @author  OptiWise Technologies B.V. <info@optiwise.nl>
 * @project SnelstartApiPHP
 */

namespace SnelstartPHP\Request\V2;

use SnelstartPHP\Exception\PreValidationException;
use function \http_build_query;
use function \array_filter;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\UuidInterface;
use SnelstartPHP\Model\V2 as Model;
use SnelstartPHP\Request\BaseRequest;
use SnelstartPHP\Request\ODataRequestDataInterface;

final class ArtikelRequest extends BaseRequest
{
    public function findAll(ODataRequestDataInterface $ODataRequestData, ?Model\Relatie $relatie = null, ?int $aantal = null): RequestInterface
    {
        return new Request("GET", "artikelen?" . $ODataRequestData->getHttpCompatibleQueryString() . '&' . $this->getQueryString($relatie, $aantal));
    }

    public function find(UuidInterface $id, ODataRequestDataInterface $ODataRequestData, ?Model\Relatie $relatie = null, ?int $aantal = null): RequestInterface
    {
        return new Request("GET", sprintf("artikelen/%s/?%s", $id->toString(), $ODataRequestData->getHttpCompatibleQueryString() . '&' . $this->getQueryString($relatie, $aantal)));
    }

    public function getCustomFields(UuidInterface $id): RequestInterface
    {
        return new Request("GET", sprintf("artikelen/%s/customFields", $id->toString()));
    }

    public function add(Model\Artikel $artikel): RequestInterface
    {
        return new Request("POST", "artikelen", [
            "Content-Type"  =>  "application/json"
        ], \GuzzleHttp\json_encode($this->prepareAddOrEditRequestForSerialization($artikel)));
    }

    public function update(Model\Artikel $artikel): RequestInterface
    {
        if ($artikel->getId() === null) {
            throw PreValidationException::shouldHaveAnIdException();
        }

        return new Request("PUT", "artikelen/" . $artikel->getId()->toString(), [
            "Content-Type"  =>  "application/json"
        ], \GuzzleHttp\json_encode($this->prepareAddOrEditRequestForSerialization($artikel)));
    }

    protected function getQueryString(?Model\Relatie $relatie = null, ?int $aantal = null): string
    {
        $relatieId = null;

        if ($relatie !== null && $relatie->getId() !== null) {
            $relatieId = $relatie->getId()->toString();
        }

        return http_build_query(array_filter([
            "relatieId" =>  $relatieId,
            "aantal"    =>  $aantal,
        ], static function($value) {
            return $value !== null;
        }), "", "&", \PHP_QUERY_RFC3986);
    }
}
