<?php

namespace RestApi\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Exception;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\ResponseInterface;
use RestApi\Serializer\ArraySerializer;
use RestApi\Utility\ApiRequestLogger;

/**
 * Application Controller
 *
 * @property \League\Fractal\Serializer\SerializerAbstract $_serializer
 *
 * @package RestApi\Controller
 */
class AppController extends Controller
{

    /**
     * HTTP Status Code
     *
     * @var int
     */
    public $httpStatusCode = 200;

    /**
     * Status value in API response
     *
     * @var string
     */
    public $responseStatus = "";

    /**
     * Response format configuration
     *
     * @var array
     */
    public $responseFormat = [];

    /**
     * API response data
     *
     * @var array
     */
    public $apiResponse = [];

    /**
     * payload value from JWT token
     *
     * @var mixed
     */
    public $jwtPayload = null;

    /**
     * JWT token for current request
     *
     * @var string
     */
    public $jwtToken = "";

    /**
     * Initialization hook method.
     *
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->responseFormat = [
            'statusKey' => (null !== Configure::read('ApiRequest.responseFormat.statusKey')) ? Configure::read('ApiRequest.responseFormat.statusKey') : 'status',
            'statusOkText' => (null !== Configure::read('ApiRequest.responseFormat.statusOkText')) ? Configure::read('ApiRequest.responseFormat.statusOkText') : 'OK',
            'statusNokText' => (null !== Configure::read('ApiRequest.responseFormat.statusNokText')) ? Configure::read('ApiRequest.responseFormat.statusNokText') : 'NOK',
            'resultKey' => (null !== Configure::read('ApiRequest.responseFormat.resultKey')) ? Configure::read('ApiRequest.responseFormat.resultKey') : 'result',
            'messageKey' => (null !== Configure::read('ApiRequest.responseFormat.messageKey')) ? Configure::read('ApiRequest.responseFormat.messageKey') : 'message',
            'defaultMessageText' => (null !== Configure::read('ApiRequest.responseFormat.defaultMessageText')) ? Configure::read('ApiRequest.responseFormat.defaultMessageText') : 'Empty response!',
            'errorKey' => (null !== Configure::read('ApiRequest.responseFormat.errorKey')) ? Configure::read('ApiRequest.responseFormat.errorKey') : 'error',
            'defaultErrorText' => (null !== Configure::read('ApiRequest.responseFormat.defaultErrorText')) ? Configure::read('ApiRequest.responseFormat.defaultErrorText') : 'Unknown request!',
        ];

        $this->responseStatus = $this->responseFormat['statusOkText'];

        $this->loadComponent('RequestHandler');
        $this->loadComponent('RestApi.AccessControl');

        if ($this->request->is('json')) {
            Configure::write('debug', false);
        }
    }

    /**
     * Before render callback.
     *
     * @param  \Cake\Event\EventInterface  $event  The beforeRender event.
     *
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        $this->getResponse()->withStatus($this->httpStatusCode);

        if (200 != $this->httpStatusCode) {
            $this->responseStatus = $this->responseFormat['statusNokText'];
        }

        $response = [
            $this->responseFormat['statusKey'] => $this->responseStatus,
        ];

        if (!empty($this->apiResponse)) {
            $response[$this->responseFormat['resultKey']] = $this->_dataToSerialize($this->apiResponse);
        }

        $this->set('response', $response);
        $this->set('responseFormat', $this->responseFormat);

        return null;
    }

    /**
     * @param $data
     *
     * @return array
     * @throws \Exception
     * @throws \Exception
     */
    protected function _dataToSerialize($data)
    {
        $serializer = $this->getSerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        if (is_array($data)) {
            foreach ($data as $varName => &$var) {
                $var = $this->transform($manager, $var);
            }
            unset($var);
        } else {
            $data = $this->transform($manager, $data);
        }

        return $data;
    }

    /**
     * @return \League\Fractal\Serializer\SerializerAbstract|\RestApi\Serializer\ArraySerializer
     */
    public function getSerializer()
    {
        if (empty($this->_serializer)) {
            return new ArraySerializer();
        }

        return $this->_serializer;
    }

    /**
     * @param  \League\Fractal\Manager  $manager
     * @param $var
     *
     * @return array|null
     * @throws \Exception
     */
    protected function transform(Manager $manager, $var)
    {
        if (!$transformer = $this->getTransformer($var)) {
            return $var;
        }

        if (is_array($var) || $var instanceof Query || $var instanceof ResultSet) {
            $resource = new Collection($var, $transformer);
        } else {
            if ($var instanceof EntityInterface) {
                $resource = new Item($var, $transformer);
            } else {
                throw new Exception('Unserializable variable');
            }
        }

        return $manager->createData($resource)->toArray();
    }

    /**
     * @param $var
     *
     * @return bool|\League\Fractal\TransformerAbstract
     * @throws \Exception
     */
    protected function getTransformer($var)
    {
        $transformerClass = $this->getTransformerClass($var);

        if ($transformerClass === false) {
            return false;
        }

        if (!class_exists($transformerClass)) {
            throw new Exception(sprintf('Invalid Transformer class: %s', $transformerClass));
        }

        $transformer = new $transformerClass;
        if (!($transformer instanceof TransformerAbstract)) {
            throw new Exception(
                sprintf(
                    'Transformer class not instance of TransformerAbstract: %s', $transformerClass
                )
            );
        }

        return $transformer;
    }

    /**
     * @param $var
     *
     * @return bool|string
     */
    protected function getTransformerClass($var)
    {
        $entity = null;
        if ($var instanceof Query) {
            $entity = $var->repository()->newEntity();
        } else {
            if ($var instanceof ResultSet) {
                $entity = $var->first();
            } else {
                if ($var instanceof EntityInterface) {
                    $entity = $var;
                } else {
                    if (is_array($var)) {
                        $entity = reset($var);
                    }
                }
            }
        }

        if (!$entity || !is_object($entity)) {
            return false;
        }

        $entityClass = get_class($entity);
        $transformerClass = str_replace('\\Model\\Entity\\', '\\Model\\Transformer\\', $entityClass).'Transformer';

        if (!class_exists($transformerClass)) {
            return false;
        }

        return $transformerClass;
    }

    /**
     * @return \Cake\Http\Response|null|void
     */
    public function shutdownProcess(): ?ResponseInterface
    {
        ApiRequestLogger::log($this->getRequest(), $this->getResponse());

        return parent::shutdownProcess();
    }
}
