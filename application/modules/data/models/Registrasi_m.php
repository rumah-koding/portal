<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Registrasi_m extends MY_Model
{
	public $table = 'users'; // you MUST mention the table name
	public $primary_key = 'id'; // you MUST mention the primary key
	public $fillable = array(); // If you want, you can set an array with the fields that can be filled by insert/update
	public $protected = array(); // ...Or you can set an array with the fields that cannot be filled by insert/update
	
	//ajax datatable
    public $column_order = array(null); //set kolom field database pada datatable secara berurutan
    public $column_search = array(); //set kolom field database pada datatable untuk pencarian
    public $order = array('id' => 'asc'); //order baku 
	
	public function __construct()
	{
		$this->timestamps = TRUE;
		$this->soft_deletes = TRUE;
		parent::__construct();
	}
	
	public function get_new()
    {
        $record = new stdClass();
        $record->id = '';
		$record->nip = '';
		//$record->username = '';
		$record->password = '';
		$record->repassword = '';
		$record->fullname = '';
		$record->email = '';
		$record->telpon = '';
		$record->pengelola_id = '';
		$record->level = '';
		$record->active = '';
        return $record;
    }
	
	//urusan lawan datatable
    private function _get_datatables_query()
    {
        $this->db->select('a.*, b.pengelola');
		$this->db->from('users a');
		$this->db->join('ref_pengelola b','a.pengelola_id = b.kode','LEFT');
		//$this->db->from($this->table);
        $i = 0;
        foreach ($this->column_search as $item) // loop column 
        {
            if($_POST['search']['value']) // if datatable send POST for search
            {
                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
 
                if(count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
         
        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
 
    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
 
    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
    
    //urusan lawan ambil data
    function get_datatables()
    {
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->where('a.deleted_at', NULL);
        $this->db->where('level', 4);
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
	
	function get_id($id=null)
    {
        $this->db->where('id', $id);
		$this->db->where('deleted_at', NULL);
        $query = $this->db->get($this->table);
        return $query->row();
    }
	
	public function get_group()
	{
        $query = $this->db->order_by('id', 'ASC')->get('groups');
        if($query->num_rows() > 0){
        $dropdown[''] = 'Pilih Group/Tingkatan Pengguna';
		foreach ($query->result() as $row)
		{
			$dropdown[$row->id] = $row->name;
		}
        }else{
            $dropdown[''] = 'Belum Ada Group/Tingkatan Pengguna Tersedia'; 
        }
		return $dropdown;
	}
	
	public function get_pengelola()
	{
		$this->db->where('deleted_at',NULL);
        $query = $this->db->order_by('kode', 'ASC')->get('ref_pengelola');
        if($query->num_rows() > 0){
        $dropdown['00000'] = 'Semua Pengelola/Urusan';
		foreach ($query->result() as $row)
		{
			$dropdown[$row->kode] = $row->kode.' - '.$row->pengelola;
		}
        }else{
            $dropdown[''] = 'Belum Ada Pengelola/Urusan Tersedia';
        }
		return $dropdown;
	}
	
	
	public function insert_data($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
    }
    
    public function get_data($nip=null)
    {
        $this->db->where('nip', $nip);
		$this->db->where('deleted_at', NULL);
        $query = $this->db->get('identitas');
        if($query->num_rows() > 0){
            return $query->row();
        }else{
            return FALSE;
        }
        
    }

    public function get_verify($nip=null)
    {
        $this->db->where('nip', $nip);
		$this->db->where('deleted_at', NULL);
        $query = $this->db->get('users');
        if($query->num_rows() > 0){
            return $query->row()->verify;
        }else{
            return FALSE;
        }
        
    }

}