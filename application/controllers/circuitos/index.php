<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php
require_once(APPPATH.VALLAS_BASE_CONTROLLER);
require_once(APPPATH.ENTITY_APIERROR);



class index extends generic_controller {
   	
    function __construct() {
        parent::__construct();
        $this->load->model('propuestas/circuito_model');
    }

    /**
     * Example Like : field1 = [%search%] & field2 = [%search & field3 = search%]
     *
     * @header Authorization
     *
     * @param offset (Opcional)
     * @param limit (Opcional)
     * @param sort (Opcional) : [field1_ASC, field2_DESC]
     * @param pagination (Opcional): {"active":1, "pageSize": 200, "page":0, "totalPages":null, "cache_token":null}
     *
     * @RequiresPermission ["circuitos", "R"]
     *
     */
    public function index_get()
    {

        $this->checkSecurity(__FUNCTION__);

        $limit = $this->get("limit");

        try {
            $result = $this->circuito_model->getAll($this->get(), $this->session->fk_pais, $limit);

            $this->response(array('result' => 'OK', 'parameters' => $result["parameters"], 'agrupaciones' => $result["agrupaciones"],'circuito' => $result["medios"]), 200);

        } catch (\Exception $e) {
            $err = new APIerror(INVALID_PROPERTY_NAME);
            $result = $err->getValues();
            $this->response(array('error' => $result), 200);
        }


    }

    /**
     *
     * @RequiresPermission ["circuitos", "C"]
     *
     */
    function index_post()
    {
        $this->checkSecurity(__FUNCTION__);

        try {

            if (sizeof($this->post()) == 0) {
                $err = new APIerror(INVALID_NUMBER_OF_PARAMS);
                $result = $err->getValues();
                $this->response(array('error' => $result), 200);
            }

            $entity = null;
            $entityPost = $this->input->post('entity');
            if ($entityPost)
                $entity = json_decode(base64_decode($entityPost));

            $array = null;
            $arrayPost = $this->input->post('array');
            if ($arrayPost)
                $array = json_decode(base64_decode($arrayPost));


            $result = $this->circuito_model->create($entity, $array, $this->session->fk_pais);
            if ($result)
                $this->response(array('result' => 'OK'), 200);
            else {
                $err = new APIerror(ERROR_SAVING_DATA);
                $result = $err->getValues();
                $this->response(array('error' => $result), 200);
            }


        } catch (\Exception $e) {
            $err = new APIerror(INVALID_PROPERTY_NAME);
            $result = $err->getValues();
            $this->response(array('error' => $result), 200);
        }
    }

    /**
     *
     * @param entityParam = value
     * @QueryParam condition
     *
     * @RequiresPermission ["circuitos", "U"]
     *
     */
    function index_put()
    {

        $this->checkSecurity(__FUNCTION__);

        //Tenemos que unir las variables de la URL y las de QueryParam
        $get_vars = array();
        if ($this->input->get() && $this->get())
            $get_vars = array_merge($this->input->get(), $this->get());
        else if ($this->input->get())
            $get_vars = $this->input->get();
        else if ($this->get())
            $get_vars = $this->get();

        try {

            if (sizeof($this->put()) == 0) {
                $err = new APIerror(INVALID_NUMBER_OF_PARAMS);
                $result = $err->getValues();
                $this->response(array('error' => $result), 200);
            }

            $result = $this->circuito_model->update($get_vars, $this->put(), $this->session->fk_pais);
            if ($result)
                $this->response(array('result' => 'OK'), 200);
            else {
                $err = new APIerror(ERROR_SAVING_DATA);
                $result = $err->getValues();
                $this->response(array('error' => $result), 200);
            }


        } catch (\Exception $e) {
            $err = new APIerror(INVALID_PROPERTY_NAME);
            $result = $err->getValues();
            $this->response(array('error' => $result), 200);
        }
    }

    /**
     *
     * @RequiresPermission ["circuitos", "D"]
     *
     */
    function index_delete()
    {
        $this->checkSecurity(__FUNCTION__);

        //Tenemos que unir las variables de la URL y las de QueryParam
        $get_vars = array();
        if ($this->input->get() && $this->get())
            $get_vars = array_merge($this->input->get(), $this->get());
        else if ($this->input->get())
            $get_vars = $this->input->get();
        else if ($this->get())
            $get_vars = $this->get();

        try {

            $result = $this->circuito_model->delete($get_vars, $this->session->fk_pais);
            if ($result)
                $this->response(array('result' => 'OK'), 200);
            else {
                $err = new APIerror(ERROR_SAVING_DATA);
                $result = $err->getValues();
                $this->response(array('error' => $result), 200);
            }


        } catch (\Exception $e) {
            $err = new APIerror(INVALID_PROPERTY_NAME);
            $result = $err->getValues();
            $this->response(array('error' => $result), 200);
        }
    }
  
}

?>