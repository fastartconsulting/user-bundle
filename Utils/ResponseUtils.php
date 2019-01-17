<?php

namespace FAC\UserBundle\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class ResponseUtils {

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var JsonResponse $response */
    private $response;

    /**
     * ResponseUtils constructor.
     * @param TranslatorInterface|object $translator
     */
    public function __construct(TranslatorInterface $translator) {
        $this->translator  = $translator;
        $this->response    = new JsonResponse();
     }

    /**
     * $message param is the source.id of the message to translate
     * $params is an array of optional parameters required for translation:
     * - array('%name%' => $name)
     *
     * @param array $results
     * @param string $message
     * @param int $status
     * @param array $params
     * @return JsonResponse
     */
    public function getResponse($results=null, $message='', $status=200, $params=array()) {
        $trans_message = $this->translator->trans($message, $params);

        if(count($results) == 0) {
            $results=null;
        }

        $this->response->setStatusCode($status);
        $this->response->setData(array(
            "status"  => $status,
            "message" => $trans_message,
            "results" => $results
        ));

        return $this->response;
    }

    /**
     * This method check if there are results and return correct json response
     *
     * @param array $results
     * @param string $message
     * @param int $status
     * @param array $params
     * @return JsonResponse
     */
    public function getListResponse($results=array(), $message='', $status=200, $params=array()){

        $trans_message = $this->translator->trans($message, $params);

        $this->response->setStatusCode($status);
        $this->response->headers->set("Access-Control-Expose-Headers", "CURRENT-PAGE, TOTAL-PAGES, TOTAL-ITEMS");

        if(is_null($results) || count($results) == 0 || !isset($results['list'])) {
            $results['list']         = array();
            $results['current_page'] = 0;
            $results['total_pages']  = 0;
            $results['total_items']  = 0;
        }

        $this->response->headers->set("CURRENT-PAGE", $results['current_page']);
        $this->response->headers->set("TOTAL-PAGES", $results['total_pages']);
        $this->response->headers->set("TOTAL-ITEMS", $results['total_items']);


        $this->response->setData(array(
            "status"  => $status,
            "message" => $trans_message,
            "results" => $results['list']
        ));

        return $this->response;
    }
}