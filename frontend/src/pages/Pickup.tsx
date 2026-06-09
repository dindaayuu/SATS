import "../styles/pickup.css";

import {
  ArrowLeft,
  Trash2,
  CheckCircle2,
  ScanLine,
  Lightbulb,
  Plus,
  AlertTriangle,
} from "lucide-react";

import { useNavigate } from "react-router-dom";
import { useState, useRef, useEffect } from "react";

interface BagItem {
  id: number;
  barcode: string;
  name_store: string;
  time: string;
  status: string;
}

export default function Pickup() {

  const navigate = useNavigate();

  const [barcode, setBarcode] = useState("");
  const [bags, setBags] = useState<BagItem[]>([]);
  const scannerInputRef = useRef<HTMLInputElement>(null);

  /*
  |--------------------------------------------------------------------------
  | MODAL
  |--------------------------------------------------------------------------
  */

  const [showModal, setShowModal] = useState(false);

  const [pickerName, setPickerName] = useState("");
      useEffect(() => {
        scannerInputRef.current?.focus();
      }, []);

  /*
  |--------------------------------------------------------------------------
  | TOAST
  |--------------------------------------------------------------------------
  */

  const [toast, setToast] = useState({
    show: false,
    type: "",
    message: "",
  });

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

  /*
  |--------------------------------------------------------------------------
  | SCAN BARCODE
  |--------------------------------------------------------------------------
  */

  const handleAddBag = async () => {

    if (!barcode.trim()) return;

    /*
    |--------------------------------------------------------------------------
    | DOUBLE SCAN
    |--------------------------------------------------------------------------
    */

    const alreadyExist = bags.find(
      (bag) => bag.barcode === barcode
    );

    if (alreadyExist) {

      showToast(
        "warning",
        "Tas sudah discan sebelumnya"
      );

      setBarcode("");

      return;
    }

    try {

      const response = await fetch(
        "http://127.0.0.1:8000/api/pickup/scan",
        {
          method: "POST",

          headers: {
            "Content-Type": "application/json",
          },

          body: JSON.stringify({
            barcode,
          }),
        }
      );

      const data = await response.json();

      /*
      |--------------------------------------------------------------------------
      | ERROR RESPONSE
      |--------------------------------------------------------------------------
      */

      if (!data.success) {

        showToast(
          "error",
          data.message
        );

        return;
      }

      /*
      |--------------------------------------------------------------------------
      | DATA TAS
      |--------------------------------------------------------------------------
      */

      const bag = data.bag;

      const newBag: BagItem = {

        id: bag.id,

        barcode: bag.barcode,

        name_store: bag.name_store,

        time: new Date().toLocaleTimeString(
          "id-ID",
          {
            hour: "2-digit",
            minute: "2-digit",
          }
        ),

        status: "Ready",
      };

      /*
      |--------------------------------------------------------------------------
      | ADD TO LIST
      |--------------------------------------------------------------------------
      */

      setBags((prev) => [...prev, newBag]);

      setBarcode("");
      
      setTimeout(() => {
        scannerInputRef.current?.focus();
      }, 50);
    } catch (error) {

      console.error(error);

      showToast(
        "error",
        "Gagal scan barcode"
      );
    }
  };

  /*
  |--------------------------------------------------------------------------
  | DELETE TAS
  |--------------------------------------------------------------------------
  */

  const handleDelete = (id: number) => {

    setBags(
      bags.filter((bag) => bag.id !== id)
    );
  
    setTimeout(() => {
      scannerInputRef.current?.focus();
    }, 50);
  };
  
  /*
  |--------------------------------------------------------------------------
  | OPEN MODAL
  |--------------------------------------------------------------------------
  */

  const handleValidatePickup = () => {

    if (bags.length === 0) {

      showToast(
        "warning",
        "Belum ada tas dipilih"
      );

      return;
    }

    setShowModal(true);
  };

  /*
  |--------------------------------------------------------------------------
  | SUBMIT VALIDASI
  |--------------------------------------------------------------------------
  */

  const submitPickup = async () => {

    if (!pickerName.trim()) {

      showToast(
        "error",
        "Nama pengambil wajib diisi"
      );

      return;
    }

    try {

      const response = await fetch(
        "http://127.0.0.1:8000/api/pickup/validate",
        {
          method: "POST",

          headers: {
            "Content-Type": "application/json",
          },

          body: JSON.stringify({
            bags,
            picker_name: pickerName,
          }),
        }
      );

      const data = await response.json();

      if (data.success) {

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        showToast(
          "success",
          "Pengambilan berhasil"
        );

        setShowModal(false);

        setPickerName("");

        setBags([]);

        /*
        |--------------------------------------------------------------------------
        | BACK TO DASHBOARD
        |--------------------------------------------------------------------------
        */

        setTimeout(() => {

          navigate("/");

        }, 1200);

      } else {

        showToast(
          "error",
          data.message || "Gagal validasi pickup"
        );
      }

    } catch (error) {

      console.error(error);

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
            onClick={() => navigate("/")}
          >
            <ArrowLeft size={18} />
          </button>

          <div>
            <h1>Pengambilan Tas</h1>
          </div>

        </div>

        <div className="selected-badge">
          {bags.length} Tas Dipilih
        </div>

      </div>

      {/* CONTENT */}

      <div className="pickup-content">

        {/* LEFT */}

        <div className="scanner-card">

          <div className="scanner-top">

            <div className="scanner-title-wrap">

              <div className="section-icon green-soft">

                <ScanLine size={18} />

              </div>

              <div>

                <h2>Scan Barcode</h2>

                <p>
                  Arahkan scanner ke barcode tas
                </p>

              </div>

            </div>

          </div>

          {/* INPUT */}

          <div className="scanner-input-wrapper">

            <div className="scanner-input green-border">

              <ScanLine
                className="scanner-input-icon"
                size={18}
              />

                <input
                  ref={scannerInputRef}
                  type="text"
                  placeholder="Scan barcode tas"                
                  value={barcode}
                onChange={(e) =>
                  setBarcode(e.target.value)
                }
                onKeyDown={(e) => {

                  if (e.key === "Enter") {

                    handleAddBag();
                  }

                }}
              />

            </div>

            <button
              className="add-button green-btn"
              onClick={handleAddBag}
            >
              <Plus size={17} />
            </button>

          </div>

          {/* TIPS */}

          <div className="tips-box">

            <div className="tips-icon">

              <Lightbulb size={15} />

            </div>

            <div>

              <h4>
                Tips: Arahkan scanner ke barcode tas
              </h4>

              <p>
                Input akan otomatis terbaca
              </p>

            </div>

          </div>

        </div>

        {/* RIGHT */}

        <div className="result-card">

          <div className="result-header">

            <div>
              <h2>Tas Terdeteksi</h2>
            </div>

            <div className="count-badge green-count">
              {bags.length}
            </div>

          </div>

          {/* LIST */}

          <div className="bag-list">

            {bags.length === 0 ? (

              <div className="empty-state">

                <AlertTriangle size={42} />

                <h3>
                  Belum ada tas discan
                </h3>

              </div>

            ) : (

              bags.map((bag) => (

                <div
                  className="bag-item"
                  key={bag.id}
                >

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

                      <CheckCircle2 size={12} />

                      {bag.status}

                    </div>

                    <button
                      className="delete-btn"
                      onClick={() =>
                        handleDelete(bag.id)
                      }
                    >
                      <Trash2 size={15} />
                    </button>

                  </div>

                </div>

              ))

            )}

          </div>

          {/* VALIDATE */}

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
      onChange={(e) =>
        setPickerName(e.target.value)
      }
    />

    <div className="modal-actions">

      <button
        className="cancel-btn"
        onClick={() =>
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

              {toast.type === "success" && "✓"}

              {toast.type === "warning" && "!"}

              {toast.type === "error" && "✕"}

            </div>

            <h3>
              {toast.message}
            </h3>

            <p>

              {toast.type === "success" &&
                "Data berhasil diproses"}

              {toast.type === "warning" &&
                "Periksa kembali data yang discan"}

              {toast.type === "error" &&
                "Barcode tidak terdaftar dalam sistem"}

            </p>

          </div>

          )}

    </div>
  );
}