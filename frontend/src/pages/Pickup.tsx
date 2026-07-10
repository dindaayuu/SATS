import "../styles/pickup.css";

import {
  ArrowLeft,
  Trash2,
  CheckCircle2,
  ScanLine,
  Lightbulb,
  Plus,
  AlertTriangle,
  ChevronDown,
  ChevronUp,
  Eye,
} from "lucide-react";

import { useNavigate } from "react-router-dom";
import {
  useState,
  useRef,
  useEffect,
} from "react";


interface AssetItem {
  id: number;
  asset: string;
  barcode: string;
}


interface BagItem {
  id: number;
  barcode: string;
  name_store: string;
  time: string;
  status: string;
  details: AssetItem[];
}


export default function Pickup() {

  const navigate =
    useNavigate();


  const [barcode, setBarcode] =
    useState("");


  const [bags, setBags] =
    useState<BagItem[]>([]);


  const [openDetail, setOpenDetail] =
    useState<number | null>(null);


  const scannerInputRef =
    useRef<HTMLInputElement>(null);


  const [showModal, setShowModal] =
    useState(false);


  const [pickerName, setPickerName] =
    useState("");


  const [toast, setToast] =
    useState({
      show: false,
      type: "",
      message: "",
    });


  useEffect(() => {

    scannerInputRef.current?.focus();

  }, []);



  const showToast = (
    type: string,
    message: string
  ) => {

    setToast({
      show: true,
      type,
      message,
    });


    setTimeout(() => {

      setToast({
        show: false,
        type: "",
        message: "",
      });

    }, 2200);
  };



  const handleAddBag =
    async () => {


      if (!barcode.trim())
        return;


      try {


        const response =
          await fetch(
            "http://127.0.0.1:8000/api/pickup/scan",
            {
              method: "POST",

              headers: {
                "Content-Type":
                  "application/json",
              },

              body:
                JSON.stringify({
                  barcode,
                }),
            }
          );


        const data =
          await response.json();



        if (!data.success) {

          showToast(
            "error",
            data.message
          );

          return;
        }


        const bag =
          data.bag;



        const alreadyExist =
          bags.find(
            (item) =>
              item.id === bag.id
          );


        if (alreadyExist) {


          showToast(
            "warning",
            "Tas sudah ada"
          );


          setBarcode("");

          return;
        }



        const detailResponse =
          await fetch(
            `http://127.0.0.1:8000/api/return/details/${bag.barcode}`
          );


        const detailData =
          await detailResponse.json();



        const newBag: BagItem = {

          id:
            bag.id,


          barcode:
            bag.barcode,


          name_store:
            bag.name_store,


          details:
            detailData.details || [],


          time:
            new Date()
              .toLocaleTimeString(
                "id-ID",
                {
                  hour:
                    "2-digit",

                  minute:
                    "2-digit",
                }
              ),


          status:
            "Ready",
        };



        setBags(
          (prev) => [
            ...prev,
            newBag,
          ]
        );


        setBarcode("");



        setTimeout(() => {

          scannerInputRef.current
            ?.focus();

        }, 50);



      } catch (error) {


        console.log(error);


        showToast(
          "error",
          "Gagal scan barcode"
        );

      }

    };




  const handleDelete =
    (id:number) => {


      setBags(
        bags.filter(
          (bag) =>
            bag.id !== id
        )
      );


    };





  const handleValidatePickup =
    () => {


      if (
        bags.length === 0
      ) {


        showToast(
          "warning",
          "Belum ada tas"
        );


        return;
      }


      setShowModal(true);

    };





  const submitPickup =
    async () => {



      if (
        !pickerName.trim()
      ) {


        showToast(
          "error",
          "Nama wajib diisi"
        );


        return;
      }



      try {


        const response =
          await fetch(
            "http://127.0.0.1:8000/api/pickup/validate",
            {
              method:"POST",

              headers:{
                "Content-Type":
                "application/json",
              },

              body:
                JSON.stringify({
                  bags,
                  picker_name:
                    pickerName,
                }),
            }
          );


        const data =
          await response.json();



        if(data.success){


          showToast(
            "success",
            "Pengambilan berhasil"
          );


          setShowModal(false);

          setBags([]);

          setPickerName("");


          setTimeout(() => {

            navigate("/");

          },1200);


        }


      }catch(error){


        console.log(error);


        showToast(
          "error",
          "Server error"
        );

      }


    };

    return (

      <div className="pickup-page">
  
  
        {/* HEADER */}
  
        <div className="pickup-header">
  
          <div className="pickup-header-left">
  
            <button
              className="back-button"
              onClick={() =>
                navigate("/")
              }
            >
  
              <ArrowLeft size={18}/>
  
            </button>
  
  
            <div>
  
              <h1>
                Pengambilan Tas
              </h1>
  
            </div>
  
          </div>
  
  
          <div className="selected-badge">
  
            {bags.length} Tas Dipilih
  
          </div>
  
        </div>
  
  
  
        {/* CONTENT */}
  
  
        <div className="pickup-content">
  
  
          {/* SCANNER */}
  
  
          <div className="scanner-card">
  
  
            <div className="scanner-top">
  
  
              <div className="scanner-title-wrap">
  
  
                <div className="section-icon green-soft">
  
                  <ScanLine size={18}/>
  
                </div>
  
  
                <div>
  
                  <h2>
                    Scan Barcode
                  </h2>
  
                  <p>
                    Arahkan scanner ke barcode tas
                  </p>
  
                </div>
  
  
              </div>
  
  
            </div>
  
  
  
  
            <div className="scanner-input-wrapper">
  
  
              <div className="scanner-input green-border">
  
  
                <ScanLine
                  size={18}
                  className="scanner-input-icon"
                />
  
  
                <input
  
                  ref={scannerInputRef}
  
                  type="text"
  
                  placeholder="Scan barcode tas"
  
                  value={barcode}
  
                  onChange={(e)=>
                    setBarcode(
                      e.target.value
                    )
                  }
  
  
                  onKeyDown={(e)=>{
  
  
                    if(
                      e.key === "Enter"
                    ){
  
                      handleAddBag();
  
                    }
  
  
                  }}
  
                />
  
  
              </div>
  
  
  
              <button
                className="add-button green-btn"
                onClick={handleAddBag}
              >
  
                <Plus size={17}/>
  
              </button>
  
  
            </div>
  
  
  
  
            <div className="tips-box">
  
  
              <div className="tips-icon">
  
                <Lightbulb size={15}/>
  
              </div>
  
  
              <div>
  
  
                <h4>
                  Tips: Scan barcode tas
                </h4>
  
  
                <p>
                  Detail isi tas akan muncul otomatis
                </p>
  
  
              </div>
  
  
            </div>
  
  
          </div>
  
  
  
  
          {/* RESULT */}
  
  
  
          <div className="result-card">
  
  
            <div className="result-header">
  
  
              <h2>
                Tas Terdeteksi
              </h2>
  
  
              <div className="count-badge green-count">
  
                {bags.length}
  
              </div>
  
  
            </div>
  
  
  
  
            <div className="bag-list">
  
  
  
              {
                bags.length === 0 ? (
  
  
                <div className="empty-state">
  
  
                  <AlertTriangle size={42}/>
  
  
                  <h3>
                    Belum ada tas discan
                  </h3>
  
  
                </div>
  
  
  
                ) : (
  
  
  
                bags.map((bag)=>(
  
  
                <div
                  className="bag-item"
                  key={bag.id}
                >

<div className="bag-header-row">


<div>

  <h3>
    {bag.barcode}
  </h3>


  <p>
    {bag.name_store}
  </p>


</div>



<div className="bag-actions">


  <div className="status-badge green">

    <CheckCircle2 size={12}/>

    {bag.status}

  </div>



  <button
    className="delete-btn"
    onClick={() =>
      handleDelete(
        bag.id
      )
    }
  >

    <Trash2 size={15}/>

  </button>



  <button
    className="detail-eye-btn"

    onClick={() =>

      setOpenDetail(

        openDetail === bag.id
        ? null
        : bag.id

      )

    }
  >


    {openDetail === bag.id ? (

      <ChevronUp size={17}/>

    ) : (

      <Eye size={17}/>

    )}


  </button>



</div>


</div>
  
                  {/* DROPDOWN DETAIL */}
  
                  {
                    openDetail === bag.id && (
  
  
  
                    <div className="pickup-detail-list">
  
  
                      {
                        bag.details.map(
                          (item)=>(
  
                            <div
                            className="pickup-detail-item"
                            key={item.id}
                          >
                          
                          <strong>

                            {item.asset}
                            {" - "}
                            {item.barcode}

                            </strong>
                          
                          
                          </div>  
  
                        ))}
  
  
                    </div>
  
  
  
                    )
                  }
  
  
  
                </div>
  
  
  
                ))
  
                )
              }
  
  
            </div>
  
  
  
  
  
            <button
  
              className="validate-btn green-btn"
  
              onClick={handleValidatePickup}
  
            >
  
  
              Validasi {bags.length} Pengambilan
  
  
            </button>
  
  
  
          </div>
  
  
  
        </div>
  
  
  
  
  
        {/* MODAL */}
  
  
  
        {showModal && (
  
  
        <div className="modal-overlay">
  
  
          <div className="modal-box">
  
  
            <h2>
              Validasi Pengambilan
            </h2>
  
  
            <p>
              Masukkan nama pengambil tas
            </p>
  
  
  
            <input
  
              type="text"
  
              placeholder="Nama pengambil"
  
              value={pickerName}
  
              onChange={(e)=>
                setPickerName(
                  e.target.value
                )
              }
  
            />
  
  
  
  
            <div className="modal-actions">
  
  
              <button
                className="cancel-btn"
  
                onClick={()=>
                  setShowModal(false)
                }
              >
  
                Batal
  
              </button>
  
  
  
  
              <button
                className="save-btn green-btn"
  
                onClick={submitPickup}
  
              >
  
                Simpan
  
              </button>
  
  
  
            </div>
  
  
          </div>
  
  
        </div>
  
  
        )}
  
  
  
  
  
        {toast.show && (
  
  
        <div className={`center-alert alert-${toast.type}`}>
  
  
          <div className="center-alert-icon">
  
  
            {toast.type==="success" && "✓"}
  
            {toast.type==="warning" && "!"}
  
            {toast.type==="error" && "✕"}
  
  
          </div>
  
  
          <h3>
  
            {toast.message}
  
          </h3>
  
  
        </div>
  
  
        )}
  
  
  
      </div>
  
    );
  
  }