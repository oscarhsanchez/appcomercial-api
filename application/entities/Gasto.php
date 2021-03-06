<?php

require_once(APPPATH.ENTITY_ESOCIAL_ENTITY);

class Gasto extends eEntity {

    public $pk_gasto;
    public $fk_ubicacion;
    public $fk_pais;
    public $unidad_negocio;
    public $anio;
    public $mes;
    public $mes_numero;
    public $departamento;
    public $importe;
    public $estado;
    public $created_at;
    public $updated_at;
    public $token;


	public function getPK() {
		return "pk_gasto";
	}

	//Este metodo los usamos para definir las propidades que queremos omitir durante la grabacion en bbdd
	public function unSetProperties() {
		return array ("created_at", "updated_at");
	}

	public function getTableName() {
		return "gastos";
	}

}

?>