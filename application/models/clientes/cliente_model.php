<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.ENTITY_CLIENTE);



class cliente_model extends CI_Model {


    /**
     * Devuelve los clientes de una entidad
     *
     * @param $entityId
     * @param $state
     * @param $offset (Opcional)
     * @param $limit (Opcional)
     * @return array
     */
    function getAll($entityId, $nomCli, $codCli, $state, $offset, $limit) {
        $this->db->where('fk_entidad', $entityId);
        $this->db->where('estado >=', $state);
        $this->db->where('bool_es_captacion', 0);
        if ($state > 0) $this->db->where('(fecha_baja IS NULL OR fecha_baja > now())');

        if ($nomCli)
            $this->db->like('nombre_comercial',"$nomCli");
        if ($codCli)
            $this->db->like('cod_cliente',"$codCli");


        if ($offset && $limit)
            $this->db->limit($limit, $offset);
        else if ($offset == 0 && $limit)
            $this->db->limit($limit, 0);

        $query = $this->db->get('clientes');

        $clientes = $query->result('cliente');

        return array("clientes" => $clientes?$clientes:array());

    }

    /**
     * Devuelve la relacion usuario cliente a partir de su token
     *
     * @param $rUsuCliToken
     * @return mixed
     */
    function getRUsuCliByToken($rUsuCliToken) {
        $this->db->where('token', $rUsuCliToken);
        $query = $this->db->get('r_usu_cli');

        $r_usu_cli = $query->row(0, 'r_usu_cli');
        return $r_usu_cli;
    }

    /**
     * Funcion encagada de guardar en la bbdd r_usu_cli
     *
     * @param $r_usu_cli
     * @return bool
     * @throws APIexception
     */
    function saveRUsuCli($r_usu_cli) {
        $this->load->model("log_model");

        if (!isset($r_usu_cli->token)) {
            $r_usu_cli->token = getToken();
        }

        $result = $r_usu_cli->_save(false, true);

        if ($result) {
            return true;
        } else {
            throw new APIexception("Error on cliente_model->saveRUsuCli. Unable to save r_usu_cli.", ERROR_SAVING_DATA, serialize($r_usu_cli));
        }
    }

    /**
     * @param $cliente
     * @return bool
     * @throws APIexception
     */
    function saveCliente($cliente, $omittedFields=null) {
        $this->load->model("log_model");

        if (!isset($cliente->token)) {
            $cliente->token = getToken();
        }

        if (!isset($cliente->pk_cliente)) {
            $cliente->setPk();
        }

        $result = $cliente->_save(false, false, $omittedFields);

        if ($result) {
            return true;
        } else {
            throw new APIexception("Error on cliente_model->saveCliente. Unable to update Cliente.", ERROR_SAVING_DATA, serialize($cliente));
        }
    }

    /**
     * @param $clientPk
     * @return $client
     *
     * Devuelve el cliente en base la clave primaria
     */
    function getClientByPk($entityId, $clientPk) {
		$this->db->where('pk_cliente', $clientPk);
        $this->db->where('fk_entidad', $entityId);
		$query = $this->db->get('clientes');

		$client = $query->row(0, 'cliente');
		return $client;

	}

    /**
     * @param $code
     * @return $client
     *
     * Devuelve el cliente en base al codigo de verificacion
     *
     *
     */
    function getClientByCodeVerification($code, $entityId) {

        if ($code != null && $code != "") {
            $this->db->where('codigo_verificacion', $code);
            $this->db->where('fk_entidad', $entityId);
            $query = $this->db->get('clientes');

            $client = $query->row(0);
            return $client;
        } else
            return null;

    }

    /**
     * @param $clientPk
     */
    function resetCodeVerification($clientPk, $entityId) {
        $q = new stdClass();
        $q->codigo_verificacion = null;
        $this->db->where('fk_entidad', $entityId);
        $this->db->where('pk_cliente', $clientPk);

        return $this->db->update('clientes', $q);
    }

    /**
     * @param $tokenTpv
     * @return $client
     *
     * Devuelve el cliente en base la clave primaria
     */
    function getClientByTokenTpv($tokenTpv, $entityId) {
        $this->db->where('token_tpv', $tokenTpv);
        $this->db->where('fk_entidad', $entityId);
        $query = $this->db->get('clientes');

        $client = $query->row(0, 'cliente');
        return $client;

    }



    /**
     * @param $clientCod
     * @param $entityId
     * @return $client
     *
     * Devuelve un cliente en base codigo y la entidad
     */
    function getClientByCod($clientCod, $entityId) {
        $this->db->where('cod_cliente', $clientCod);
        $this->db->where('fk_entidad', $entityId);
        $query = $this->db->get('clientes');

        $client = $query->row(0, 'cliente');
        return $client;

    }

    /**
     * Devuelve la relacion entre un VENDEDOR y un cliente activa.
     *
     * @param $clientPk
     * @param $userPk
     * @return r_usu_cli
     */
    function getRUsuCliByClienteAndVendedor($clientPk, $userPk) {
        $this->db->where('fk_cliente', $clientPk);
        $this->db->where('fk_usuario_vendedor', $userPk);
        $this->db->where('estado > 0');
        $query = $this->db->get('r_usu_cli');

        $r_usu_cli = $query->row(0, 'r_usu_cli');
        return $r_usu_cli;

    }

    /**
     * @param $entityPk
     *
     * Coge el siguiente numero de cliente y actualiza la tabla
     */
    function getCode($entityPk) {

        $this->db->trans_start();

        $this->db->where('fk_entidad', $entityPk);
        $query = $this->db->get('code');

        $result = $r_usu_cli = $query->row(0);

        if ($result) {
            $q = new stdClass();
            $q->last_cliente = $result->last_cliente + 1;
            $this->db->where('fk_entidad', $entityPk);

            $this->db->update('code', $q);

            $this->db->trans_complete();

            return str_pad($result->last_cliente + 1, 7, "0", STR_PAD_LEFT);

        } else {
            return null;
        }

    }

    /**
     * Devuelve r_usu_cli a partir de un canal de venta.
     *
     * @param $canalVentaPk
     *
     * @return r_usu_cli
     */
    function getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk) {
        $this->db->where('fk_canal_venta', $canalVentaPk);
        $this->db->where('fk_cliente', $clientPk);
        $this->db->where('estado > 0');
        $query = $this->db->get('r_usu_cli');

        $r_usu_cli = $query->row(0, 'r_usu_cli');
        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un vendedor.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     *
     * @return r_usu_cli
     */
    function setVendedor($fk_entidad, $clientPk, $userPk, $canalVentaPk) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_vendedor = $userPk;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_vendedor = $userPk;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un vendedor a partir de una fecha programada.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     * @param $fromDate
     * @param $toDate
     *
     * @return r_usu_cli
     */
    function setVendedorFromDate($fk_entidad, $clientPk, $userPk, $canalVentaPk, $fromDate, $toDate) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_receptor_vendedor = $userPk;
            $r_usu_cli->fecha_vendedor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_vendedor_hasta = $toDate;
            else
                $r_usu_cli->fecha_vendedor_hasta = null;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_receptor_vendedor = $userPk;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->fecha_vendedor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_vendedor_hasta = $toDate;
            else
                $r_usu_cli->fecha_vendedor_hasta = null;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un repartidor.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     *
     * @return r_usu_cli
     */
    function setRepartidor($fk_entidad, $clientPk, $userPk, $canalVentaPk) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_repartidor = $userPk;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_repartidor = $userPk;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un repartidor a partir de una fecha programada.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     * @param $fromDate
     * @param $toDate
     *
     * @return r_usu_cli
     */
    function setRepartidorFromDate($fk_entidad, $clientPk, $userPk, $canalVentaPk, $fromDate, $toDate) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_receptor_repartidor = $userPk;
            $r_usu_cli->fecha_repartidor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_repartidor_hasta = $toDate;
            else
                $r_usu_cli->fecha_repartidor_hasta = null;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_receptor_repartidor = $userPk;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->fecha_repartidor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_repartidor_hasta = $toDate;
            else
                $r_usu_cli->fecha_repartidor_hasta = null;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un autoventa.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     *
     * @return r_usu_cli
     */
    function setAutoventa($fk_entidad, $clientPk, $userPk, $canalVentaPk) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_vendedor = $userPk;
            $r_usu_cli->fk_usuario_repartidor = $userPk;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_vendedor = $userPk;
            $r_usu_cli->fk_usuario_repartidor = $userPk;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una relacion entre un cliente y un autoventa a partir de una fecha programada.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $userPk
     * @param $canalVentaPk
     * @param $fromDate
     * @param $toDate
     *
     * @return r_usu_cli
     */
    function setAutoventaFromDate($fk_entidad, $clientPk, $userPk, $canalVentaPk, $fromDate, $toDate) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            $r_usu_cli->fk_usuario_receptor_vendedor = $userPk;
            $r_usu_cli->fk_usuario_receptor_repartidor = $userPk;

            $r_usu_cli->fecha_vendedor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_vendedor_hasta = $toDate;
            else
                $r_usu_cli->fecha_vendedor_hasta = null;
            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_usuario_receptor_vendedor = $userPk;
            $r_usu_cli->fk_usuario_receptor_repartidor = $userPk;

            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->fk_cliente = $clientPk;
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->fecha_vendedor_desde = $fromDate;
            if ($toDate)
                $r_usu_cli->fecha_vendedor_hasta = $toDate;
            else
                $r_usu_cli->fecha_vendedor_hasta = null;
            $r_usu_cli->estado = 1;
            $r_usu_cli->tipo_frecuencia = 0;
            $r_usu_cli->token = getToken();
            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }


    /**
     * Establece la frecuencia de visita para un cliente y un canal de venta.
     *
     * @param $fk_entidad
     * @param $clientPk
     * @param $rUsuCli
     * @param $canalVentaPk
     *
     * @return r_usu_cli
     */
    function setFrecuenciaVisita($fk_entidad, $clientPk, $rUsuCli, $canalVentaPk) {

        $r_usu_cli = $this->getRUsuCliByCanalVentaAndCliente($clientPk, $canalVentaPk);

        if ($r_usu_cli) {

            if ($rUsuCli->tipo_frecuencia) $r_usu_cli->tipo_frecuencia = $rUsuCli->tipo_frecuencia;
            $r_usu_cli->dia_1 = $rUsuCli->dia_1;
            $r_usu_cli->dia_2 = $rUsuCli->dia_2;
            $r_usu_cli->dia_3 = $rUsuCli->dia_3;
            $r_usu_cli->dia_4 = $rUsuCli->dia_4;
            $r_usu_cli->dia_5 = $rUsuCli->dia_5;
            $r_usu_cli->dia_6 = $rUsuCli->dia_6;
            $r_usu_cli->dia_7 = $rUsuCli->dia_7;
            if ($rUsuCli->hora) $r_usu_cli->hora = $rUsuCli->hora;
            if ($rUsuCli->hora_reparto) $r_usu_cli->hora_reparto = $rUsuCli->hora_reparto;
            if ($rUsuCli->tipo_mensual) $r_usu_cli->tipo_mensual = $rUsuCli->tipo_mensual;
            if ($rUsuCli->repetir_cada) $r_usu_cli->repetir_cada = $rUsuCli->repetir_cada;
            if ($rUsuCli->values_mes) $r_usu_cli->values_mes = $rUsuCli->values_mes;

            $r_usu_cli->_save(false, true);

        } else {

            $r_usu_cli = new r_usu_cli();
            $r_usu_cli->fk_entidad = $fk_entidad;
            $r_usu_cli->fk_canal_venta = $canalVentaPk;
            $r_usu_cli->estado = 1;
            $r_usu_cli->token = getToken();
            if ($rUsuCli->tipo_frecuencia) $r_usu_cli->tipo_frecuencia = $rUsuCli->tipo_frecuencia;
            $r_usu_cli->dia_1 = $rUsuCli->dia_1;
            $r_usu_cli->dia_2 = $rUsuCli->dia_2;
            $r_usu_cli->dia_3 = $rUsuCli->dia_3;
            $r_usu_cli->dia_4 = $rUsuCli->dia_4;
            $r_usu_cli->dia_5 = $rUsuCli->dia_5;
            $r_usu_cli->dia_6 = $rUsuCli->dia_6;
            $r_usu_cli->dia_7 = $rUsuCli->dia_7;
            if ($rUsuCli->hora) $r_usu_cli->hora = $rUsuCli->hora;
            if ($rUsuCli->hora_reparto) $r_usu_cli->hora_reparto = $rUsuCli->hora_reparto;
            if ($rUsuCli->tipo_mensual) $r_usu_cli->tipo_mensual = $rUsuCli->tipo_mensual;
            if ($rUsuCli->repetir_cada) $r_usu_cli->repetir_cada = $rUsuCli->repetir_cada;
            if ($rUsuCli->values_mes) $r_usu_cli->values_mes = $rUsuCli->values_mes;

            $r_usu_cli->_save(false, true);

        }

        return $r_usu_cli;

    }

    /**
     * Establece una forma de pago para un cliente
     *
     * @param $clientPk
     * @param $formaPagoPk
     *
     * @return cliente
     */
    function setFormaPago($clientPk, $formaPagoPk) {
        $cliente = $this->getClientByPk($clientPk);

        if ($cliente) {
            $cliente->fk_forma_pago = $formaPagoPk;
            $cliente->_save(false, false);
        }

        return $cliente;

    }

    /**
     * Establece a un cliente como asigando genericamente
     *
     * @param $clientPk
     * @param $formaPagoPk
     *
     * @return cliente
     */
    function setASignacionGenerica($clientPk, $asignacion) {
        $cliente = $this->getClientByPk($clientPk);

        if ($cliente) {
            $cliente->bool_asignacion_generica = $asignacion;
            $cliente->_save(false, false);
        }

        return $cliente;

    }






    /**
     * @param $userPk
     * @param $entityId
     * @param $pagination --> $pageSize, $page, $totalPages, $pagination
     * @param $cache_token
     * @param int $state
     * @param null $lastTimeStamp
     * @return array($pagination, array($clientes))
     *
     * Funcion que devuelve los clientes asignados a un usuarios (Vendedor o repartidor)
     * paginados y opcionalmente a partir de una fecha de actualizacion.
     * El parametro state establece el estado de los clientes a devolver. Estado >= $state
     * El resultado se trocea y se guarda en memcache esperando las proximas llamadas
     *
     */
    function getMultipartCachedClientsAssignedToUser($userPk, $entityId, $pagination, $state=0, $lastTimeStamp=null) {

        $this->load->library('esocialmemcache');

        if (isset($pagination->cache_token)) {
            $key = $pagination->cache_token . "-" . $pagination->page;
            $clients = unserialize($this->esocialmemcache->get($key));
            if (!$clients) throw new APIexception("Error Getting Data From Memcache", ERROR_GETTING_INFO, serialize($pagination));
        } else {
            $query = $this->getAssignedQuery($entityId, $userPk, $state, $lastTimeStamp);

            $query = $this->db->query($query);

            $clients = $query->result();

            $rowcount = sizeof($clients);
            if ($rowcount >= MULTIPART_CACHE_PAGINATION_MAX_SIZE) return null;

            $pagination->totalPages = ceil($rowcount / $pagination->pageSize);
            $pagination->page = 0;
            
            for ($i=0; $i<count($clients); $i++) {
            	$r_usu_cli = new r_usu_cli();
                $r_usu_cli->estado = $clients[$i]->estado_asi;
                $r_usu_cli->token = $clients[$i]->token_asi;
            	$r_usu_cli->set($clients[$i]);
            	$cliente = new cliente();
                $cliente->estado = $clients[$i]->estado_cli;
                $cliente->token = $clients[$i]->token_cli;
            	$cliente->set($clients[$i]);
            	$clients[$i] = array('cliente' => $cliente, 'r_usu_cli' => $r_usu_cli);
            }

            if ($rowcount > $pagination->pageSize) {
                $chunk_clients = array_chunk($clients, $pagination->pageSize);

                $clients = $chunk_clients[0];

                $fecha = new DateTime();
                $pagination->cache_token = base64_encode(rand() . '-' . $fecha->getTimestamp() . '-' . rand());

                for ($i=1; $i < sizeof($chunk_clients); $i++) {
                    $this->esocialmemcache->add($pagination->cache_token . "-" . $i, serialize($chunk_clients[$i]), false, MULTIPART_CACHE_PAGINATION_EXPIRE_TIME);
                }
            }
        }

        return array("pagination" => $pagination, "clientes" => $clients?$clients:array());

    }

    /**
     * @param $entityId
     * @param $pagination --> $pageSize, $page, $totalPages, $pagination
     * @param $cache_token
     * @param int $state
     * @param null $lastTimeStamp
     * @return array($pagination, array($clientes))
     *
     * Funcion que devuelve los clientes de una entidad
     * paginados y opcionalmente a partir de una fecha de actualizacion.
     * El parametro state establece el estado de los clientes a devolver. Estado >= $state
     * El resultado se trocea y se guarda en memcache esperando las proximas llamadas
     *
     */
    function getMultipartCachedClients($entityId, $pagination, $state=0, $lastTimeStamp=null) {

        $this->load->library('esocialmemcache');

        if (isset($pagination->cache_token)) {
            $key = $pagination->cache_token . "-" . $pagination->page;
            $clients = unserialize($this->esocialmemcache->get($key));
            if (!$clients) throw new APIexception("Error Getting Data From Memcache", ERROR_GETTING_INFO, serialize($pagination));
        } else {
            $query = "SELECT * FROM clientes WHERE fk_entidad = ".$entityId." AND estado >= ".$state." AND updated_at >= '".$lastTimeStamp."' AND (".$state."=0 OR clientes.fecha_baja IS NULL OR clientes.fecha_baja > NOW()) ";

            $query = $this->db->query($query);

            $clients = $query->result('cliente');

            $rowcount = sizeof($clients);
            if ($rowcount >= MULTIPART_CACHE_PAGINATION_MAX_SIZE) return null;

            $pagination->totalPages = ceil($rowcount / $pagination->pageSize);
            $pagination->page = 0;

            if ($rowcount > $pagination->pageSize) {
                $chunk_clients = array_chunk($clients, $pagination->pageSize);

                $clients = $chunk_clients[0];

                $fecha = new DateTime();
                $pagination->cache_token = base64_encode(rand() . '-' . $fecha->getTimestamp() . '-' . rand());

                for ($i=1; $i < sizeof($chunk_clients); $i++) {
                    $this->esocialmemcache->add($pagination->cache_token . "-" . $i, serialize($chunk_clients[$i]), false, MULTIPART_CACHE_PAGINATION_EXPIRE_TIME);
                }
            }
        }

        return array("pagination" => $pagination, "clientes" => $clients?$clients:array());

    }

    /**
     * @param $entityId
     * @param $field
     * @param $query
     * @param $return
     * @param type
     * @return Array(Value, Description)
     */
    function search($entityId, $field, $query, $return=null, $type='text') {
        if (!$return) {
            $return = 'pk_cliente';
        }

        $this->db->select($return.' AS value,'.$field.' AS description', false);
        $this->db->where('fk_entidad', $entityId);
        $this->db->where('bool_es_captacion', 0, false);
        if ($type == 'text')
            $this->db->like($field, $query);
        else
            $this->db->where($field, $query);

        $this->db->limit(10);
        $query = $this->db->get('clientes');

        $result = $query->result();
        return $result?$result:array();
    }

    /**
     * @param $entityId
     * @param $field
     * @param $query
     * @param $return
     * @param $type
     * @param $userId
     * @return Array(Value, Description)
     */
    function userAssignedsearch($entityId, $field, $query, $return=null, $type='text', $userId) {
        if (!$return) {
            $return = 'pk_cliente';
        }

        $this->db->select('DISTINCT '.$return.' AS value,'.$field.' AS description', false);
        $this->db->from('clientes');
        $this->db->join('r_usu_cli', 'r_usu_cli.fk_cliente = clientes.pk_cliente');
        $this->db->join('r_usu_emp', 'r_usu_emp.pk_usuario_entidad = r_usu_cli.fk_usuario_vendedor OR r_usu_emp.pk_usuario_entidad = r_usu_cli.fk_usuario_repartidor');
        $this->db->where('clientes.fk_entidad', $entityId);
        $this->db->where('r_usu_emp.id_usuario', $userId);
        $this->db->where('bool_es_captacion', 0, false);
        if ($type == 'text')
            $this->db->like($field, $query);
        else
            $this->db->where($field, $query);

        $this->db->limit(10);
        $query = $this->db->get();

        $result = $query->result();
        return $result?$result:array();
    }
	
}

?>