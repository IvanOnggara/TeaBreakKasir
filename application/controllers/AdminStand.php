<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class AdminStand extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct(){
	    parent::__construct();
	    $this->load->helper('url');
	    $this->load->helper('site_helper');
	    $this->load->model('ModelKasir');
	    $this->load->library('session');
  	}

  	public function login()
  	{
  		
  		$json = @file_get_contents('http://teabreak.bekkostudio.com/getDataStan');
		// $json = @file_get_contents('http://localhost/teabreak/getDataStan');
		if($json === FALSE){
			echo "<p class='red'>(warning) tidak bisa tersambung ke server !</p>";
		}else{
			$datas = json_decode($json);
			$localdatastan = $this->ModelKasir->getSpecificColumn('stan','id_stan');
			$onlinedatastan = array();
			// var_dump($localdatastan);

			foreach ($datas as $data) {
				$exist = $this->ModelKasir->checkExist('stan',$data->id_stan);
				$array = array(
			        'id_stan' => $data->id_stan,
			        'nama_stan' => $data->nama_stan,
			        'alamat' => $data->alamat,
			        'password' => $data->password
			    );

				if ($exist) {
					$where = array(
				        'id_stan' => $data->id_stan
				    );
					$this->ModelKasir->update('stan', $array, $where);
				}else{
					$this->ModelKasir->insert('stan',$array);
				}
				array_push($onlinedatastan,$data->id_stan);
			}

			foreach ($localdatastan as $perstan) {
				if (!in_array($perstan->id_stan, $onlinedatastan)) {
					$this->ModelKasir->delete('stan',$perstan->id_stan);
				}
			}


			echo "<p class='green'>(success) data stan terupdate</p>";
		}
  		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            $this->load->view('adminstand/login');
        }else{
        	redirect('kasir');
        }
  	}

  	public function prosesLogin()
  	{
  		$username = $this->input->post('username');
  		$password = $this->input->post('password');
  		$where = array('id_stan' => $username,'password' => $password);
  		
  		if ($this->ModelKasir->getRowCount('stan',$where) > 0) {
  			$uss = $this->ModelKasir->getData($where,'stan');
  			$this->session->set_userdata('aksesadminstan', 'granted');
  			$this->session->set_userdata('id_stan', $username);
  			$this->session->set_userdata('alamat_stan', $uss[0]->alamat);
  		 	echo 'true';
  		}else{
  			echo "false";
  			
  		} 
  	}

  	public function logout()
  	{
  		$this->session->unset_userdata('aksesadminstan');
  		$this->session->unset_userdata('id_stan');
  		$this->session->unset_userdata('update');
  		$this->session->unset_userdata('alamat_stan');
  		redirect('login');
  	}

	public function kasir()
	{
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$updated = $this->session->userdata('update');

        	if (empty($updated)) {
        		$status = 'true';

        		//DATA PRODUK
        		
        		$json = @file_get_contents('http://teabreak.bekkostudio.com/getDataProduk');
        		// $json = @file_get_contents('http://localhost/teabreak/getDataProduk');
				if($json === FALSE){
					
					$status = 'false';
				}else{
					$datas = json_decode($json);
					$localdataproduk = $this->ModelKasir->getSpecificColumn('produk','id_produk');
					$onlinedataproduk = array();
					// var_dump($localdataproduk);

					foreach ($datas as $data) {
						$exist = $this->ModelKasir->checkExist('produk',$data->id_produk);
						$array = array(
					        'id_produk' => $data->id_produk,
					        'nama_produk' => $data->nama_produk,
					        'kategori' => $data->kategori,
					        'harga_jual' => $data->harga_jual
					    );

						if ($exist) {
							$where = array(
						        'id_produk' => $data->id_produk
						    );
							$this->ModelKasir->update('produk', $array, $where);
						}else{
							$this->ModelKasir->insert('produk',$array);
						}
						array_push($onlinedataproduk,$data->id_produk);
					}

					foreach ($localdataproduk as $perproduk) {
						if (!in_array($perproduk->id_produk, $onlinedataproduk)) {
							$this->ModelKasir->delete('produk',$perproduk->id_produk);
						}
					}
					
				}


				$postdata = http_build_query(
				    array(
				        'id_stan' => $this->session->userdata('id_stan')
				    )
				);

				$opts = array('http' =>
				    array(
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => $postdata
				    )
				);

				$context  = stream_context_create($opts);



				//DATA DISKON
				
				$json = @file_get_contents('http://teabreak.bekkostudio.com/getDataDiskon', false, $context);
				// $json = @file_get_contents('http://localhost/teabreak/getDataDiskon', false, $context);
				if($json === FALSE){
					
					$status = 'false';
				}else{
					// var_dump($json);
					$datas = json_decode($json);
					$localdatadiskon = $this->ModelKasir->getSpecificColumn('diskon','id_diskon');
					$onlinedatadiskon = array();
					// var_dump($localdataproduk);

					if (!empty($datas)) {
						foreach ($datas as $data) {
							$exist = $this->ModelKasir->checkExist('diskon',$data->id_diskon);
							$array = array(
						        'id_diskon' => $data->id_diskon,
						        'nama_diskon' => $data->nama_diskon,
						        'jenis_diskon' => $data->jenis_diskon,
						        'tanggal_mulai' => $data->tanggal_mulai,
						        'tanggal_akhir' => $data->tanggal_akhir,
						        'jam_mulai' => $data->jam_mulai,
						        'jam_akhir' => $data->jam_akhir,
						        'hari' => $data->hari,
						        'status' => $data->status,
						    );

							if ($exist) {
								$where = array(
							        'id_diskon' => $data->id_diskon
							    );
								$this->ModelKasir->update('diskon', $array, $where);
							}else{
								$this->ModelKasir->insert('diskon',$array);
							}
							array_push($onlinedatadiskon,$data->id_diskon);
						}

						foreach ($localdatadiskon as $perproduk) {
							if (!in_array($perproduk->id_diskon, $onlinedatadiskon)) {
								$this->ModelKasir->delete('diskon',$perproduk->id_diskon);
							}
						}
					}else{
						$this->ModelKasir->deleteAllData('diskon');
					}
					
				}

				//DATA DETAIL DISKON (BARANG)
				
				$json = @file_get_contents('http://teabreak.bekkostudio.com/getDataDetailDiskonProduk', false, $context);
				// $json = @file_get_contents('http://localhost/teabreak/getDataDetailDiskonProduk', false, $context);
				if($json === FALSE){
					
					$status = 'false';
				}else{
					$datas = json_decode($json);
					$localdatadetailbarangdiskon = $this->ModelKasir->getSpecificColumn('detail_barang_diskon','id_diskon,id_produk');
					$onlinedatadetailbarangdiskon = array();
					// var_dump($localdataproduk);
					if (!empty($datas)) {
						foreach ($datas as $data) {
							$where = array('id_diskon' => $data->id_diskon,'id_produk' => $data->id_produk );
							$exist = $this->ModelKasir->checkExistDetailBarangDiskon($where);
							$array = array(
								'id_diskon' => $data->id_diskon,
						        'id_produk' => $data->id_produk
						    );

							if (!$exist) {
								$this->ModelKasir->insert('detail_barang_diskon',$array);
							}
							array_push($onlinedatadetailbarangdiskon,[$data->id_diskon,$data->id_produk]);
						}

						foreach ($localdatadetailbarangdiskon as $perdetailproduk) {
							if (!in_array([$perdetailproduk->id_diskon,$perdetailproduk->id_produk], $onlinedatadetailbarangdiskon)) {
								$where2 = array('id_diskon' => $perdetailproduk->id_diskon,'id_produk' => $perdetailproduk->id_produk );
								$this->ModelKasir->deleteWithCustomWhere('detail_barang_diskon', $where2);
							}
						}
					}else{
						$this->ModelKasir->deleteAllData('detail_barang_diskon');
					}
					
				}


				//GET DATA BAHAN JADI TERBARU
				$json = @file_get_contents('http://teabreak.bekkostudio.com/getDataBahanJadi', false, $context);
				// $json = @file_get_contents('http://localhost/teabreak/getDataBahanJadi', false, $context);
				if($json === FALSE){
					
					$status = 'false';
				}else{
					$datas = json_decode($json);
					$localdatabahanjadi = $this->ModelKasir->getSpecificColumn('bahan_jadi','id_bahan_jadi');
					$onlinedatabahanjadi = array();
					// var_dump($localdataproduk);
					if (!empty($datas)) {
						foreach ($datas as $data) {
							$exist = $this->ModelKasir->checkExist('bahan_jadi',$data->id_bahan_jadi);
							$array = array(
						        'id_bahan_jadi' => $data->id_bahan_jadi,
						        'nama_bahan_jadi' => $data->nama_bahan_jadi
						    );

							if ($exist) {
								$where = array(
							        'id_bahan_jadi' => $data->id_bahan_jadi
							    );
								$this->ModelKasir->update('bahan_jadi', $array, $where);
							}else{
								$this->ModelKasir->insert('bahan_jadi',$array);
							}
							array_push($onlinedatabahanjadi,$data->id_bahan_jadi);
						}

						foreach ($localdatabahanjadi as $perbahanjadi) {
							if (!in_array($perbahanjadi->id_bahan_jadi, $onlinedatabahanjadi)) {
								$this->ModelKasir->delete('bahan_jadi',$perbahanjadi->id_bahan_jadi);
							}
						}
					}else{
						$this->ModelKasir->deleteAllData('bahan_jadi');
					}
					
					
				}

				if ($status == 'true') {
					$this->session->set_userdata('update','updated');
					echo "<p class='green'>(success) seluruh data telah terupdate</p>";
				}else{
					echo "<p class='red'>(warning) tidak bisa tersambung ke server !</p>";
				}

        	}

        	$this->load->view('adminstand/header');
            $this->load->view('adminstand/kasir');
        }
		
	}

	public function order()
	{
		// $json = @file_get_contents('http://teabreak.bekkostudio.com/getDataOrder');
		$json = @file_get_contents('http://localhost/teabreak/getDataOrder');
		if($json === FALSE){
			echo "<p class='red'>(warning) tidak bisa tersambung ke server !</p>";
		}else{
			// $datas = json_decode($json);
			// $localdatastan = $this->ModelKasir->getSpecificColumn('stan','id_stan');
			// $onlinedatastan = array();
			// // var_dump($localdatastan);

			// foreach ($datas as $data) {
			// 	$exist = $this->ModelKasir->checkExist('stan',$data->id_stan);
			// 	$array = array(
			//         'id_stan' => $data->id_stan,
			//         'nama_stan' => $data->nama_stan,
			//         'alamat' => $data->alamat,
			//         'password' => $data->password
			//     );

			// 	if ($exist) {
			// 		$where = array(
			// 	        'id_stan' => $data->id_stan
			// 	    );
			// 		$this->ModelKasir->update('stan', $array, $where);
			// 	}else{
			// 		$this->ModelKasir->insert('stan',$array);
			// 	}
			// 	array_push($onlinedatastan,$data->id_stan);
			// }

			// foreach ($localdatastan as $perstan) {
			// 	if (!in_array($perstan->id_stan, $onlinedatastan)) {
			// 		$this->ModelKasir->delete('stan',$perstan->id_stan);
			// 	}
			// }


			echo "<p class='green'>(success) data order terupdate</p>";
		}

		$this->load->view('adminstand/header');
        $this->load->view('adminstand/order');
	}

	public function getAllKategori()//GET KATEGORI
	{
		$where = array('kategori !=' => 'topping' );
		$data = $this->ModelKasir->getDistinctSpecificColumnWhere('produk','kategori',$where);
		echo json_encode($data);
	}

	public function getProdukInKategori() //GET PRODUK DI KATEGORI TERTENTU
	{
		$kategori = $this->input->post('kategori');
		$where = array('kategori' => $kategori );
		$data = $this->ModelKasir->getData($where,'produk');
		echo json_encode($data);
	}

	public function getListTopping() //GET LIST TOPPING SAJA
	{
		$where = array('kategori' => 'topping' );
		$data = $this->ModelKasir->getData($where,'produk');
		echo json_encode($data);
	}

	public function getDiskon()//GET DISKON SETIAP PILIH PRODUK ATAU TAMBAH PRODUK ATAU KURANGI PRODUK
	{
		date_default_timezone_set("Asia/Bangkok");
		$datenow = date("Y-m-d");
		$daynow = date("w");
		$timenow = date("H:i:s");
		switch ($daynow) {
			case 0:
				$daynow = 'minggu';
				break;
			case 1:
				$daynow = 'senin';
				break;
			case 2:
				$daynow = 'selasa';
				break;
			case 3:
				$daynow = 'rabu';
				break;
			case 4:
				$daynow = 'kamis';
				break;
			case 5:
				$daynow = 'jumat';
				break;
			case 6:
				$daynow = 'sabtu';
				break;
			
			default:
				break;
		}
		$daynow = "%".$daynow."%";

		$id_produk = $this->input->post('id');
		$where = array('produk.id_produk' => $id_produk);
		$arraytoui = array();


		// $alldiskon = $this->ModelKasir->getDataDiskonForProduct($where);
		$wheretanggal = array(
			'tanggal_mulai<='=>$datenow,
			'tanggal_akhir>='=>$datenow,
			'hari LIKE'=>$daynow,
			'jam_mulai<='=>$timenow,
			'jam_akhir>='=>$timenow,
		);

		$alldiskon = $this->ModelKasir->getData($wheretanggal,'diskon');
		// $alldiskon = $this->ModelKasir->getAllData('diskon');

		foreach ($alldiskon as $diskon) {
			// $where2 = array('diskon.id_diskon' => $diskon->id_diskon);
			$where2 = array('diskon.id_diskon' => $diskon->id_diskon);
			$listbarang = $this->ModelKasir->getListProductForDiskon($where2);
			$produk2nya = '';
			$first = true;
			foreach ($listbarang as $produk2) {
				if ($first) {
					$produk2nya = $produk2nya.$produk2->id_produk;
					$first = false;
				}else{
					$produk2nya = $produk2nya.','.$produk2->id_produk;
				}
			}
			array_push($arraytoui, array('id_diskon' => $diskon->id_diskon,'nama_diskon' => $diskon->nama_diskon, 'jenis_diskon' => $diskon->jenis_diskon,'id_poduk'=> $produk2nya));
		}

		
		echo json_encode($arraytoui);
	}

	public function saveNota()
	{
		$dataorder = json_decode($this->input->post('order'));
		var_dump($dataorder);
		$list_diskon = $this->input->post('list_diskon');
		$harga_akhir = $this->input->post('harga_akhir');
		$tipe_pembayaran = $this->input->post('tipe_pembayaran');
		$keterangan = $this->input->post('keterangan');

		$diskon = $this->ModelKasir->getDataInTable('diskon',$list_diskon,'id_diskon');
		$arraynamadiskon = array();
		$arrayjenisdiskon = array();

		foreach ($diskon as $perdiskon) {
			array_push($arraynamadiskon, $perdiskon->nama_diskon);
			array_push($arrayjenisdiskon, $perdiskon->jenis_diskon);
		}

		date_default_timezone_set("Asia/Bangkok");
		$idnota = $this->session->userdata('id_stan').IDNotaGenerator();
		$datesave= date("Y-m-d");
		$timesave = date("H:i");

		if (empty(array_filter($arraynamadiskon))) {
			$namadisk = 'none';
			$jenisdisk = 'none';
		}else{
			$namadisk = implode(',', $arraynamadiskon);
			$jenisdisk = implode(',', $arrayjenisdiskon);
		}

		$data = array(
			'id_nota' => $idnota,
			'tanggal_nota' => $datesave,
			'waktu_nota' => $timesave, 
			'nama_diskon' => $namadisk,
			'jenis_diskon' => $jenisdisk,
			'status' => 'novoid',
			'total_harga' => $harga_akhir,
			'pembayaran' => $tipe_pembayaran,
			'keterangan' => $keterangan,
			'status_upload' => 'not_upload'
		);

		// var_dump($dataorder);
		$this->ModelKasir->insert('nota',$data);
		$listidproduk = array();
		$listjumlahproduk = array();
		$listidprodukdiskon = array();
		$arraydiskonprod = array();
		$listall = array();

		foreach ($dataorder as $perorder) {
			if (!in_array($perorder->id_produk, $listidproduk)) {
				array_push($listidproduk, $perorder->id_produk);
				array_push($listjumlahproduk, $perorder->qty);	 	   
			}else{
				for ($i=0; $i < count($listidproduk); $i++) { 
					if ($listidproduk[$i] == $perorder->id_produk) {
						$listjumlahproduk[$i] +=  $perorder->qty;
					}
				}
			}

			// if ($perorder->diskon>0) {
				if (!array_key_exists($perorder->id_produk, $arraydiskonprod)) {
				    $arraydiskonprod[$perorder->id_produk] = 0;
				}
				$arraydiskonprod[$perorder->id_produk] = $arraydiskonprod[$perorder->id_produk] + $perorder->diskon;
			// }

			foreach ($perorder->list_idtopping as $pertopping) {
				if (!in_array($pertopping, $listidproduk)) {
					array_push($listidproduk, $pertopping);
					array_push($listjumlahproduk, $perorder->qty);	 	   
				}else{
					for ($i=0; $i < count($listidproduk); $i++) { 
						if ($listidproduk[$i] == $pertopping) {
							$listjumlahproduk[$i] +=  $perorder->qty;
						}
					}
				}

				if (!array_key_exists($pertopping, $arraydiskonprod)) {
				    $arraydiskonprod[$pertopping] = 0;
				}
				$arraydiskonprod[$pertopping] = $arraydiskonprod[$pertopping] + $perorder->diskon;
			}
		}
		$angkaid = 1;
		for ($i=0; $i < count($listidproduk); $i++) {
			$whereprod = array('id_produk'=>$listidproduk[$i]);
			$dataprod = $this->ModelKasir->getData($whereprod,'produk');
			
			$id_detail_nota = $this->session->userdata('id_stan')."".IDDetailNotaGenerator()."ke".$angkaid;

			$datadetail = array(
				'id_detail_nota' => $id_detail_nota,
				'id_nota' => $idnota,
				'nama_produk' => $dataprod[0]->nama_produk,
				'jumlah_produk' => $listjumlahproduk[$i],
				'kategori_produk' => $dataprod[0]->kategori,
				'harga_produk' => $dataprod[0]->harga_jual,
				'total_harga_produk' => intval($listjumlahproduk[$i])*intval($dataprod[0]->harga_jual)-$arraydiskonprod[$listidproduk[$i]]
			);
			$this->ModelKasir->insert('detail_nota',$datadetail);
			$angkaid+=1;
			array_push($listall, $datadetail);
		}
		// var_dump($listall);
		// var_dump($data);

		//SAVE NOTA

		$this->sinkronnota();
	}

	public function sinkronnota()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('nota',$whereforsinkron) <1) {
			echo "SUCCESSSAVE";
		}else{
			$listnotabelumupload = $this->ModelKasir->getData($whereforsinkron,'nota');
			$listnotarray = array();

			foreach ($listnotabelumupload as $pernota) {
				array_push($listnotarray, $pernota->id_nota);
			}
			$listalldetailnota = $this->ModelKasir->getDataIn('detail_nota',$listnotarray);
			

			$postdata = http_build_query(
			    array(
			        'allnota' => json_encode($listnotabelumupload),
			        'detailnota' => json_encode($listalldetailnota),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataNota', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataNota', false, $context);
			if($send === FALSE){
				echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					// var_dump($send);
					foreach ($listnotabelumupload as $nota) {
						$where = array('id_nota' => $nota->id_nota );
						$update = array('status_upload' => 'upload' );
						$this->ModelKasir->update('nota',$update,$where);
						
					}
					echo "SUCCESSSAVE";
				}else{
					echo "PENYIMPANANGAGAL";
				}
			}
		}
		
	}

	public function getListNota()
	{
		$where = array('status' => 'novoid');
		$data = $this->ModelKasir->getData($where,'nota');
		echo json_encode($data);
	}

	public function voidNota()
	{
		$id = $this->input->post('id_nota');
		$password = $this->input->post('pwd');
		$id_stan = $this->session->userdata('id_stan');

		$where = array('id_stan' => $id_stan,'password' => $password);
  		
  		if ($this->ModelKasir->getRowCount('stan',$where) > 0) {
  			$where2 = array('id_nota' => $id);
			$data = array('status' => 'void' );

			$this->ModelKasir->update('nota', $data, $where2);
  		 	echo 'true';
  		}else{
  			echo "false";
  		} 
	}

	public function viewvoidnota(){

        $akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/notavoid');
        }
	}

	public function stokmasuk(){
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/stokmasuk');
        }
	}

	public function stokkeluar(){
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/stokkeluar');
        }
	}

	public function laporanstok(){
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/laporanstok');
        }
	}

	public function pengeluaranlain(){
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/pengeluaranlain');
        }
	}

	public function orderproduk(){
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/orderproduk');
        }
	}

	public function sisastok()
	{
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/sisastok');
        }
	}

	public function getNamaBahanJadi()
	{
		$data = $this->ModelKasir->getAllData('bahan_jadi');
		echo json_encode($data);
	}

	public function dataStokMasuk()
	{
		$this->load->library('datatables');
		$this->datatables->select('id_bahan_jadi,nama_bahan_jadi,stok_masuk,tanggal');
		$this->datatables->from('stok_bahan_jadi');
		echo $this->datatables->generate();
	}

	public function tambah_stok_masuk()
	{
		$id = $this->input->post('id');
		$nama = $this->input->post('nama');
		$stokmasuk = $this->input->post('stokmasuk');

		$datenow = date("Y-m-d");

		$where = array('id_bahan_jadi' => $id, 'tanggal' => $datenow);
		$count = $this->ModelKasir->getRowCount('stok_bahan_jadi',$where);

		$whereLastItem = array('id_bahan_jadi' => $id);
		$countThatItem = $this->ModelKasir->getRowCount('stok_bahan_jadi',$whereLastItem);

		if ($countThatItem>0) {
			$dataLast = $this->ModelKasir->getDataWhereDesc('stok_bahan_jadi',$whereLastItem,'tanggal');
			$stoksisa = $dataLast[0]->stok_sisa;
		}else{
			$stoksisa = 0;
		}

		if ($count>0) {
			$dataBeforeUpdate = $this->ModelKasir->getData($where,'stok_bahan_jadi');
			$stoksisabefore = $dataBeforeUpdate[0]->stok_sisa - $dataBeforeUpdate[0]->stok_masuk;
			$data = array(
		        'stok_masuk' => $stokmasuk,
		        'stok_sisa' => $stoksisabefore+$stokmasuk,
		        'status_upload' => 'not_upload'
	         );

			$this->ModelKasir->update('stok_bahan_jadi', $data, $where);
			$this->sinkronstokbahan();
			echo "Data telah di update!.";
		}else{
			$data = array(
		        'id_bahan_jadi' => $id,
		        'nama_bahan_jadi' => $nama,
		        'stok_masuk' => $stokmasuk,
		        'stok_keluar' => 0,
		        'stok_sisa' => $stoksisa+$stokmasuk,
		        'tanggal' => $datenow,
		        'status_upload' => 'not_upload'
	         );

			$this->ModelKasir->insert('stok_bahan_jadi',$data);
			$this->sinkronstokbahan();
			echo "Berhasil Ditambahkan";
		}

		
	}

	public function dataStokKeluar()
	{
		$this->load->library('datatables');
		$this->datatables->select('id_bahan_jadi,nama_bahan_jadi,stok_keluar,tanggal');
		$this->datatables->from('stok_bahan_jadi');
		echo $this->datatables->generate();
	}

	public function tambah_stok_keluar()
	{
		$id = $this->input->post('id');
		$nama = $this->input->post('nama');
		$stokkeluar = $this->input->post('stokkeluar');

		$datenow = date("Y-m-d");

		$where = array('id_bahan_jadi' => $id, 'tanggal' => $datenow);
		$count = $this->ModelKasir->getRowCount('stok_bahan_jadi',$where);

		$whereLastItem = array('id_bahan_jadi' => $id);
		$countThatItem = $this->ModelKasir->getRowCount('stok_bahan_jadi',$whereLastItem);

		if ($countThatItem>0) {
			$dataLast = $this->ModelKasir->getDataWhereDesc('stok_bahan_jadi',$whereLastItem,'tanggal');
			$stoksisa = $dataLast[0]->stok_sisa;
		}else{
			$stoksisa = 0;
		}

		if ($count>0) {
			$dataBeforeUpdate = $this->ModelKasir->getData($where,'stok_bahan_jadi');
			$stoksisabefore = $dataBeforeUpdate[0]->stok_sisa + $dataBeforeUpdate[0]->stok_keluar;
			$data = array(
		        'stok_keluar' => $stokkeluar,
		        'stok_sisa' => $stoksisabefore-$stokkeluar,
		        'status_upload' => 'not_upload'
	         );

			$this->ModelKasir->update('stok_bahan_jadi', $data, $where);
			$this->sinkronstokbahan();
			echo "Data telah di update!.";
		}else{
			$data = array(
		        'id_bahan_jadi' => $id,
		        'nama_bahan_jadi' => $nama,
		        'stok_masuk' => 0,
		        'stok_keluar' => $stokkeluar,
		        'stok_sisa' => $stoksisa-$stokkeluar,
		        'tanggal' => $datenow,
		        'status_upload' => 'not_upload'
	         );

			$this->ModelKasir->insert('stok_bahan_jadi',$data);
			$this->sinkronstokbahan();
			echo "Berhasil Diatur";
		}
	}

	public function sinkronstokbahan()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('stok_bahan_jadi',$whereforsinkron) <1) {
			if ($this->input->post('sst') == 'sinkron') {
				echo "SUCCESSSAVE";
			}
			// echo "SUCCESSSAVE";
		}else{
			$liststokbelumupload = $this->ModelKasir->getData($whereforsinkron,'stok_bahan_jadi');
			// var_dump($liststokbelumupload);

			$postdata = http_build_query(
			    array(
			        'allstok' => json_encode($liststokbelumupload),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataStok', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataStok', false, $context);
			if($send === FALSE){
				if ($this->input->post('sst') == 'sinkron') {
					echo "CANTCONNECT";
				}
				// echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					// var_dump($send);
					$update = array('status_upload' => 'upload');
					$wherenot = array('status_upload' => 'not_upload');
					$this->ModelKasir->update('stok_bahan_jadi',$update,$wherenot);
					if ($this->input->post('sst') == 'sinkron') {
						echo "SUCCESSSAVE";
					}
					// echo "SUCCESSSAVE";
				}else{
					if ($this->input->post('sst') == 'sinkron') {
						echo "PENYIMPANANGAGAL";
					}
					// echo "PENYIMPANANGAGAL";
				}
			}
			// var_dump($send);
		}
	}

	public function webservice($port,$url,$parameter){
		$curl = curl_init();
		set_time_limit(0);
		curl_setopt_array($curl, array(
			CURLOPT_PORT => $port,
			CURLOPT_URL => "http://".$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $parameter,
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"content-type: application/x-www-form-urlencoded"
				),
			)
		);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			$response = ("Error #:" . $err);
		}
		else
		{
			$response;
		}
		return $response;
	}

	public function sinkronpresensi()
	{
		$getdata = $this->ModelKasir->getDataLimit('device_finger',1);
		$parameter = "sn=".$getdata[0]->sn."&limit=100";
		$port = $getdata[0]->port;

		if ($this->ModelKasir->getCountAllData('presensi_karyawan') > 0) {
			$urlscan = $getdata[0]->ip."/scanlog/new";
			// $alluserdatabase = $this->ModelKasir->getAllData('presensi_karyawan');
		}else{
			$urlscan1 = $getdata[0]->ip."/scanlog/new";
			$a = $this->webservice($port,$urlscan1,$parameter);

			$urlscan = $getdata[0]->ip."/scanlog/all/paging";
				
		}

		$server_output_scan = $this->webservice($port,$urlscan,$parameter);		
		$content_allnewscan = json_decode($server_output_scan);

		if ($content_allnewscan == NULL) {
			echo "CANTCONNECT";
		}else{
			if ($content_allnewscan->Result == false) {
				echo "string";
			}else{
				foreach ($content_allnewscan->Data as $scan) {
					$data = array(
						'scan_date'=>$scan->ScanDate,
						'pin' => $scan->PIN,
						'verify_mode' => $scan->VerifyMode,
						'io_mode' => $scan->IOMode,
						'work_code' => $scan->WorkCode,
						'status_upload' =>'not_upload'
					);

					$this->ModelKasir->insert('presensi_karyawan',$data);
				}
				echo "SUCCESSSAVE";
			}
		}

		
		
	}

	public function sinkronuser()
	{
		$statkosong = true;
		$getdata = $this->ModelKasir->getDataLimit('device_finger',1);
		$parameter = "sn=".$getdata[0]->sn."&limit=100";
		$port = $getdata[0]->port;
			
		$url = $getdata[0]->ip."/user/all/paging";
		$server_output = $this->webservice($port,$url,$parameter);		
		$content_alluser = json_decode($server_output);

		$alluserdatabase = $this->ModelKasir->getAllData('karyawan_fingerspot');

		if ($this->ModelKasir->getCountAllData('karyawan_fingerspot') > 0) {
			$statkosong = false;
		}

		if ($content_alluser == NULL) {
			echo "CANTCONNECT";
		}else{
			if ($content_alluser->Result == false) {
				echo "NONEWDATA";
			}else{
				if ($statkosong) {
					foreach ($content_alluser->Data as $user) {
						$data = array(
							'pin' => $user->PIN,
							'nama' => $user->Name,
							'status_upload' => 'not_upload'
						);

						$this->ModelKasir->insert('karyawan_fingerspot',$data);
					}
				}else{
					$listdata_user_finger = array();
					$adauser = false;
					foreach ($content_alluser->Data as $user) {
						foreach ($alluserdatabase as $peruserdatabase) {
							if ($user->PIN == $peruserdatabase->pin) {
								if ($user->Name != $peruserdatabase->nama) {
									$where = array('pin' => $peruserdatabase->pin);
									$data = array(
										'nama' => $user->Name,
										'status_upload' => 'not_upload'
									);
									$this->ModelKasir->update('karyawan_fingerspot', $data, $where);
								}
								$adauser = true;
							}
						}

						if (!$adauser) {
							$data = array(
								'pin' => $user->PIN,
								'nama' => $user->Name,
								'status_upload' => 'not_upload'
							);

							$this->ModelKasir->insert('karyawan_fingerspot',$data);
						}
						array_push($listdata_user_finger,$user->PIN);
					}

					foreach ($alluserdatabase as $peruserdatabase) {
						if (!in_array($peruserdatabase->pin, $listdata_user_finger)) {
							$where = array('pin' => $peruserdatabase->pin);
							$this->ModelKasir->deleteWhere('karyawan_fingerspot',$where);
						}
					}
				}
				echo "SUCCESSSAVE";
			}
		}
	}

	public function sinkronpresensionline()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('presensi_karyawan',$whereforsinkron) <1) {
			echo "SUCCESSSAVE";
		}else{
			$listpresensibelumupload = $this->ModelKasir->getData($whereforsinkron,'presensi_karyawan');

			$postdata = http_build_query(
			    array(
			        'allpresensi' => json_encode($listpresensibelumupload),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataPresensiKaryawan', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataPresensiKaryawan', false, $context);
			if($send === FALSE){
				echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					$update = array('status_upload' => 'upload');
					$wherenot = array('status_upload' => 'not_upload');
					$this->ModelKasir->update('presensi_karyawan',$update,$wherenot);
					echo "SUCCESSSAVE";
				}else{
					echo "PENYIMPANANGAGAL";
				}
			}
		}
	}

	public function sinkronuseronline()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('karyawan_fingerspot',$whereforsinkron) <1) {
			echo "SUCCESSSAVE";
		}else{
			$listkaryawanbelumupload = $this->ModelKasir->getData($whereforsinkron,'karyawan_fingerspot');

			$postdata = http_build_query(
			    array(
			        'allkaryawan' => json_encode($listkaryawanbelumupload),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataKaryawanFingerspot', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataKaryawanFingerspot', false, $context);
			if($send === FALSE){
				echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					$update = array('status_upload' => 'upload');
					$wherenot = array('status_upload' => 'not_upload');
					$this->ModelKasir->update('karyawan_fingerspot',$update,$wherenot);
					echo "SUCCESSSAVE";
				}else{
					echo "PENYIMPANANGAGAL";
				}
			}
		}
	}

	public function dataSisaStok()
	{
		$tanggal = $this->input->post('tanggal');

		if ($tanggal == '') {
			$where = array('tanggal' => '');
			$this->load->library('datatables');
			$this->datatables->select('id_bahan_jadi,nama_bahan_jadi,stok_masuk,stok_keluar,stok_sisa');
			$this->datatables->from('stok_bahan_jadi');
			$this->datatables->where($where);
			
			echo $this->datatables->generate();
		}else{
			$tanggal = strtotime($tanggal);
			$tanggal = date('Y-m-d',$tanggal);

			$where = array('tanggal' => $tanggal);
			// $alldata = $this->ModelKasir->getData($where,'stok_bahan_jadi');

			// return json_encode($alldata);

			$this->load->library('datatables');
			$this->datatables->select('id_bahan_jadi,nama_bahan_jadi,stok_masuk,stok_keluar,stok_sisa');
			$this->datatables->from('stok_bahan_jadi');
			$this->datatables->where($where);
			
			echo $this->datatables->generate();
		}
	}

	public function dataPengeluaranLain()
	{
		$this->load->library('datatables');
		$this->datatables->select('id_pengeluaran,tanggal,keterangan,pengeluaran');
		$this->datatables->from('pengeluaran_lain');
		echo $this->datatables->generate();
	}

	public function tambah_pengeluaran_lain()
	{
		$keterangan = $this->input->post('keterangan');
		$jumlahpengeluaran = $this->input->post('jumlahpengeluaran');

		$datenow = date("Y-m-d");
		
		$data = array(
			'tanggal' => $datenow,
	        'keterangan' => $keterangan,
	        'pengeluaran' => $jumlahpengeluaran,
	        'status_upload' => 'not_upload'
         );

		$this->ModelKasir->insert('pengeluaran_lain',$data);
		$this->sinkronpengeluaran();
		echo "Berhasil Ditambahkan";
		
	}

	public function edit_pengeluaran_lain()
	{
		$keteranganbaru = $this->input->post('keteranganbaru');
		$pengeluaranbaru = $this->input->post('pengeluaranbaru');
		$id_pengeluaran = $this->input->post('id_pengeluaran');

		$where = array('id_pengeluaran' => $id_pengeluaran);

		$data = array(
			'keterangan' => $keteranganbaru,
	        'pengeluaran' => $pengeluaranbaru,
	        'status_upload' => 'not_upload'
         );

		$realdata = $this->ModelKasir->getData($where,'pengeluaran_lain');

		if ($realdata[0]->keterangan != $keteranganbaru || $realdata[0]->pengeluaran != $pengeluaranbaru) {
			$cek = $this->ModelKasir->Update('pengeluaran_lain',$data,$where);
		}else{
			$cek = true;
		}

		if ($cek) {
			echo "Berhasil Diupdate";
		}else{
			echo "gagal";
		}
		$this->sinkronpengeluaran();
		
	}

	public function delete_pengeluaran()
	{
		$id_pengeluaran = $this->input->post('id');

		$postdata = http_build_query(
		    array(
		        'id_pengeluaran' => $id_pengeluaran,
		        'id_stan' => $this->session->userdata('id_stan')
		    )
		);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);

		$context  = stream_context_create($opts);
		//DATA NOTA
		$send = @file_get_contents('http://teabreak.bekkostudio.com/deleteDataPengeluaran', false, $context);
		// $send = @file_get_contents('http://localhost/teabreak/deleteDataPengeluaran', false, $context);
		if($send === FALSE){
			if ($this->input->post('sst') == 'sinkron') {
				echo "CANTCONNECT";
			}
			// echo 'CANTCONNECT';
		}else{
			if ($send == 'true') {
				// var_dump($send);
				$wheredel = array('id_pengeluaran' => $id_pengeluaran);
				$this->ModelKasir->deleteWhere('pengeluaran_lain',$wheredel);
				if ($this->input->post('sst') == 'sinkron') {
					echo "SUCCESSSAVE";
				}
				// echo "SUCCESSSAVE";
			}else{
				if ($this->input->post('sst') == 'sinkron') {
					echo "PENYIMPANANGAGAL";
				}
				// echo "PENYIMPANANGAGAL";
			}
		}
		
		
		// $this->sinkronpengeluaran();
		//perlu perbaikan disini
	}

	public function kasawal()
	{
		$akses = $this->session->userdata('aksesadminstan');
        if(empty($akses)){
            redirect('login');
        }else{
        	$this->load->view('adminstand/header');
			$this->load->view('adminstand/kasawal');
        }

	}

	public function cekDataKas()
	{
		$datenow = date("Y-m-d");
		$where = array('tanggal' => $datenow);
		if ($this->ModelKasir->getRowCount('kas',$where) > 0) {
			
		}else{
			$array = array(
				'tanggal' => $datenow,
				'kas_awal' => 0,
				'status_upload' => 'not_upload'
			);

			$this->ModelKasir->insert('kas',$array);
		}

		$data = $this->ModelKasir->getData($where,'kas');
		echo $data[0]->kas_awal;
		$this->sinkronkas();
	}

	public function simpankas()
	{
		$kasbaru = $this->input->post('kas');
		$datenow = date("Y-m-d");
		$where = array('tanggal' => $datenow);
		$data = array('kas_awal' => $kasbaru, 'status_upload' => 'not_upload');
		$this->ModelKasir->Update('kas',$data,$where);
		echo "sukses";
		$this->sinkronkas();
	}

	public function sinkronpengeluaran()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('pengeluaran_lain',$whereforsinkron) <1) {
			if ($this->input->post('sst') == 'sinkron') {
				echo "SUCCESSSAVE";
			}
			// echo "SUCCESSSAVE";
		}else{
			$listpengeluaranbelumupload = $this->ModelKasir->getData($whereforsinkron,'pengeluaran_lain');
			// var_dump($liststokbelumupload);

			$postdata = http_build_query(
			    array(
			        'allpengeluaran' => json_encode($listpengeluaranbelumupload),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataPengeluaran', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataPengeluaran', false, $context);
			if($send === FALSE){
				if ($this->input->post('sst') == 'sinkron') {
					echo "CANTCONNECT";
				}
				// echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					// var_dump($send);
					$update = array('status_upload' => 'upload');
					$wherenot = array('status_upload' => 'not_upload');
					$this->ModelKasir->update('pengeluaran_lain',$update,$wherenot);
					if ($this->input->post('sst') == 'sinkron') {
						echo "SUCCESSSAVE";
					}
					// echo "SUCCESSSAVE";
				}else{
					if ($this->input->post('sst') == 'sinkron') {
						echo "PENYIMPANANGAGAL";
					}
					// echo "PENYIMPANANGAGAL";
				}
			}
			// var_dump($send);
		}
	}

	public function sinkronkas()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('kas',$whereforsinkron) <1) {
			if ($this->input->post('sst') == 'sinkron') {
				echo "SUCCESSSAVE";
			}
			// echo "SUCCESSSAVE";
		}else{
			$listkasbelumupload = $this->ModelKasir->getData($whereforsinkron,'kas');

			$postdata = http_build_query(
			    array(
			        'allkas' => json_encode($listkasbelumupload),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			$send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataKas', false, $context);
			// $send = @file_get_contents('http://localhost/teabreak/insertDataKas', false, $context);
			if($send === FALSE){
				if ($this->input->post('sst') == 'sinkron') {
					echo "CANTCONNECT";
				}
				// echo 'CANTCONNECT';
			}else{
				if ($send == 'true') {
					// var_dump($send);
					$update = array('status_upload' => 'upload');
					$wherenot = array('status_upload' => 'not_upload');
					$this->ModelKasir->update('kas',$update,$wherenot);
					if ($this->input->post('sst') == 'sinkron') {
						echo "SUCCESSSAVE";
					}
					// echo "SUCCESSSAVE";
				}else{
					if ($this->input->post('sst') == 'sinkron') {
						echo "PENYIMPANANGAGAL";
					}
					// echo "PENYIMPANANGAGAL";
				}
			}
			// var_dump($send);
		}
	}

	public function printnota()
	{

		// try {
		$pelanggan = $this->input->post('pelanggan');
		$alamat = $this->session->userdata('alamat_stan');
		$order = json_decode($this->input->post('order'));
		$subtotal = $this->input->post('subtotal');
		$diskon = $this->input->post('diskon');
		$totalakhir = $this->input->post('pembayaran');
		$kembalian = $this->input->post('kembalian');
		// var_dump($order);

		  $this->load->library('ReceiptPrint');
		  $this->receiptprint->connect('MINIPOS');
		  $this->receiptprint->print_test_receipt($order,$pelanggan,$alamat,$subtotal,$diskon,$totalakhir,$kembalian);
		// } catch (Exception $e) {
		//   log_message("error", "Error: Could not print. Message ".$e->getMessage());
		//   echo "error";
		//   $this->receiptprint->close_after_exception();
		// }

		// require_once __DIR__ . 'assets/vendor/mike42/escpos-php/autoload.php';
		// use Mike42\Escpos\Printer;
		// use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
		// try {
		//     // Enter the share name for your USB printer here
		//     $connector = new WindowsPrintConnector("MINIPOS"); //MINIPOS adalah nama shared printernya
		//     /* Print a "Hello world" receipt" */
		//     $printer = new Printer($connector);
		//     $printer -> text("Teh Matcha super nikmat terbuat dari daun asli\n");
		//     $printer -> text("Rp 10.000\n");
		//     $printer -> text("aaaaa\n");
		//     $printer -> cut();
		    
		//     /* Close printer */
		//     $printer -> close();
		// } catch (Exception $e) {
		//     echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
		// }
	}

	public function presensi()
	{
		$this->load->view('adminstand/header');
        $this->load->view('adminstand/presensi');
	}

	public function saveOrder()
	{
		$dataorder = json_decode($this->input->post('order'));

		$idorder = IDOrderGenerator($this->session->userdata('id_stan'));

		date_default_timezone_set("Asia/Bangkok");
		$datenow = date("Y-m-d");

		$data = array(
			'id_order' => $idorder, 
			'tanggal_order' => $datenow,
			'status' => 'not_done',
			'status_upload' => 'not_upload'
		);
		$this->ModelKasir->insert('order_bahan_jadi_stan',$data);
		$num = 0;

		foreach ($dataorder as $peritem) {
			$datadetail = array(
				'id_detail_order' => $idorder.'_'.$num,
				'id_order' => $idorder,
				'nama_bahan_jadi' => $peritem->nama_bahan_jadi,
				'jumlah' => $peritem->qty
			);
			$num++;

			$this->ModelKasir->insert('detail_order_bahan_jadi_stan',$datadetail);
		}

		
		//sinkron order
		$this->sinkronorder();
		var_dump($dataorder);
	}

	public function sinkronorder()
	{
		$whereforsinkron = array('status_upload' => 'not_upload');

		if ($this->ModelKasir->getRowCount('order_bahan_jadi_stan',$whereforsinkron) <1) {
			echo "SUCCESSSAVE";
		}else{
			$listorderbelumupload = $this->ModelKasir->getData($whereforsinkron,'order_bahan_jadi_stan');
			$listnotarray = array();

			foreach ($listorderbelumupload as $perorder) {
				array_push($listnotarray, $perorder->id_order);
			}
			$listalldetailorder = $this->ModelKasir->getDataInTable('detail_order_bahan_jadi_stan',$listnotarray,'id_order');
			

			$postdata = http_build_query(
			    array(
			        'allorder' => json_encode($listorderbelumupload),
			        'detailorder' => json_encode($listalldetailorder),
			        'id_stan' => $this->session->userdata('id_stan')
			    )
			);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);
			//DATA NOTA
			// $send = @file_get_contents('http://teabreak.bekkostudio.com/insertDataNota', false, $context);
			$send = @file_get_contents('http://localhost/teabreak/insertDataOrder', false, $context);
			if($send === FALSE){
				if ($this->input->post('sst') == 'sinkron') {
					echo "CANTCONNECT";
				}
			}else{
				if ($send == 'true') {
					// var_dump($send);
					foreach ($listorderbelumupload as $order) {
						$where = array('id_order' => $order->id_order );
						$update = array('status_upload' => 'upload' );
						$this->ModelKasir->update('order_bahan_jadi_stan',$update,$where);
						
					}

					if ($this->input->post('sst') == 'sinkron') {
						echo "SUCCESSSAVE";
					}
					
				}else{
					if ($this->input->post('sst') == 'sinkron') {
						echo "PENYIMPANANGAGAL";
					}
					
				}
			}
		}
	}

	public function listorder()
	{
		// $json = @file_get_contents('http://teabreak.bekkostudio.com/getDataOrder');
		$json = @file_get_contents('http://localhost/teabreak/getDataOrder');
		if($json === FALSE){
			echo "<p class='red'>(warning) tidak bisa tersambung ke server !</p>";
		}else{
			// $datas = json_decode($json);
			// $localdatastan = $this->ModelKasir->getSpecificColumn('stan','id_stan');
			// $onlinedatastan = array();
			// // var_dump($localdatastan);

			// foreach ($datas as $data) {
			// 	$exist = $this->ModelKasir->checkExist('stan',$data->id_stan);
			// 	$array = array(
			//         'id_stan' => $data->id_stan,
			//         'nama_stan' => $data->nama_stan,
			//         'alamat' => $data->alamat,
			//         'password' => $data->password
			//     );

			// 	if ($exist) {
			// 		$where = array(
			// 	        'id_stan' => $data->id_stan
			// 	    );
			// 		$this->ModelKasir->update('stan', $array, $where);
			// 	}else{
			// 		$this->ModelKasir->insert('stan',$array);
			// 	}
			// 	array_push($onlinedatastan,$data->id_stan);
			// }

			// foreach ($localdatastan as $perstan) {
			// 	if (!in_array($perstan->id_stan, $onlinedatastan)) {
			// 		$this->ModelKasir->delete('stan',$perstan->id_stan);
			// 	}
			// }


			echo "<p class='green'>(success) data order terupdate</p>";
		}

		$this->load->view('adminstand/header');
        $this->load->view('adminstand/listorder');
	}
}
