<?php

require_once(APPPATH.ENTITY_ESOCIAL_ENTITY);

class PropuestaDetalle extends eEntity {

    public $pk_propuesta_detalle;
    public $fk_propuesta;
    public $fk_pais;
    public $fk_plaza;
    public $fk_ubicacion;
    public $fk_tipo;
    public $fk_subtipo;
    public $clasificacion;
    public $unidad_negocio;
    public $tipo_negociacion;
    public $moneda;
    public $precio;
    public $tipo_cambio;
    public $cantidad;
    public $total;
    public $estado;
    public $created_at;
    public $updated_at;
    public $token;

    /**
     * @ORM\Relation ["PropuestaDetalleOutdoor", "array"]
     */
    public $detalle_outdoor;


	public function getPK() {
		return "pk_propuesta_detalle";
	}

	//Este metodo los usamos para definir las propidades que queremos omitir durante la grabacion en bbdd
	public function unSetProperties() {
		return array ("created_at", "updated_at", "detalle_outdoor");
	}

	public function getTableName() {
		return "propuestas_detalle";
	}

}

?>