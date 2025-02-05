import React, {  useState, useRef, useEffect, Fragment } from "react";
import axios from 'axios';
import qs from 'qs';

export const OrderMessage = () => {
    const [messages, setMessages] = useState({
    });
  
    const obj = document.getElementById('wps_rma_order_msg_react');
    const orderID = obj.dataset.order_id;
  
    const enableSmsNotification = wps_rma_react_object.wps_rma_enable_sms_notification;
    const enableSmsForCustomer = wps_rma_react_object.wps_rma_enable_sms_notification_for_customer;
    const uploadAttach = wps_rma_react_object.upload_attach;

    const screenID = wps_rma_react_object.is_admin;

    const containerRef = useRef(null);
    
    // format the data.
    const extractMessages = (data) => {
      return data.map((item) => {
        const [key] = Object.keys(item);
        var data = item[key];

        const date = new Date(key * 1000);

        const formattedDate = new Intl.DateTimeFormat('en-US', {
          year: 'numeric',
          month: 'long',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true,
        }).format(date);
        data.date = formattedDate;
        return data;
      });
    };
  
    // fetch the order messages.
    const fetchOrderMessage = () => {
      const sendData = {
        action: 'wps_rma_fetch_order_msgs',
        nonce: wps_rma_react_object.wps_rma_react_nonce,
        order_id : orderID,
      };
    
      axios.post(wps_rma_react_object.ajaxurl, qs.stringify(sendData))
        .then(res => {
          if (res.data) {
            const Extractmessages = extractMessages(res.data);
            setMessages(Extractmessages);
          }
        })
        .catch(error => {
          console.error('Error fetching order messages data:', error);
      });
    }

    const Redirect = () => {
      const container = containerRef.current;

      const scrollAmount = container.scrollHeight - container.clientHeight;
  
      // Animate scrollTop
      container.animate(
        [
          { scrollTop: container.scrollTop },
          { scrollTop: scrollAmount },
        ],
        {
          duration: 200,
          easing: 'ease-in-out',
        }
      );
      container.scrollTop = scrollAmount;
    }
  
    useEffect(() => {
      // Initial fetch
      fetchOrderMessage();
      
      // Polling every 5 seconds
      const interval = setInterval(fetchOrderMessage, 5000);
      
      if (containerRef.current) {
        setTimeout(() => {
          Redirect();
       }, 500);
      }
      return () => clearInterval(interval); // Cleanup on unmount

      
    }, [setMessages]);

  
    const [formData, setFormData] = useState({
      wps_order_new_msg: "",
      wps_order_msg_attachment: null,
      wps_rma_customer_contact_order_message: "",
    });
    
    // handling form data 
    const handleChange = (e) => {
      const { name, value } = e.target;  
      setFormData({ ...formData, [name]: value });
    };

    const handleFileChange = (event) => {
      const { name, files } = event.target;
      setFormData({ ...formData, [name]: Array.from(files)});
    };
  
    // submit the form data
    const handleSubmit = async (e) => {
      e.preventDefault();

      const data = new FormData();
      data.append("msg", formData.wps_order_new_msg);
      data.append("order_id", orderID);
      data.append("nonce", wps_rma_react_object.wps_rma_react_nonce);
      data.append("action", 'wps_rma_send_order_msg');
      data.append("order_msg_type", screenID );
      data.append("wps_rma_customer_contact_order_message", formData.wps_rma_customer_contact_order_message );
      
      if ( formData.wps_order_msg_attachment ) {
        formData.wps_order_msg_attachment.forEach((file, index) => {
          data.append(`wps_order_msg_attachment[]`, file); // Append each file
        });
      }
      try {
      const response = await axios.post( wps_rma_react_object.ajaxurl, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.status === 200) {
        fetchOrderMessage();

        setFormData({
          wps_order_new_msg: "",
          wps_order_msg_attachment: null,
          wps_rma_customer_contact_order_message: "",
        });
        setTimeout(() => {
          Redirect();
        }, 100);
      } else {
        console.error("Error:", response);
      }
      } catch (error) {
        console.error("Error:", error);
      }
    };

    return (
      <Fragment>
          <div className="wps_order_msg_container">
            <div className="wps-order-msg_column">
              <div className="wps-order-msg_column_name shop_man-title">{wps_rma_react_object.shop_manager}</div>
              <div className="wps-order-msg_column_name">{wps_rma_react_object.customer}</div>
            </div>
            <div className="wps_order_msg_sub_container" ref={containerRef}>
              {messages && messages.length > 0 && messages.map((data, index) => {
                let processedText = data.sender.toLowerCase().replace(/\s+/g, '');
                return (
                  <div className={`wps-order-msg_row_${processedText} wps-order-msg_row`} key={`${index}`}>
                    <div
                      className={`wps_order_msg_main_container wps_order_messages ${
                        data.sender === "Customer"
                          ? "wmb-order-customer__msg-container"
                          : "wmb-order-admin__msg-container"
                      }`}
                    >
                      <div className="wps_order_msg_sender_details">
                        <span className="wps_order_msg_date">{data.date}</span> {/* Using the key as a date */}
                      </div>
                    </div>
                    <div className="wps_order_msg_detail_container">
                      <span>{data.msg}</span>
                    </div>
                    {data.files && data.files.length > 0 && (
                      <>
                        <div className="wps_order_msg_attach_container">
                          {data.files.map((file, fileIndex) => (
                            <div className="wps_order_msg_single_attachment" key={fileIndex}>
                              <a
                                target="_blank"
                                rel="noopener noreferrer"
                                href={file.img ? `${wps_rma_react_object.attachment_url + file.name}` : "attachment.png"}
                              >
                                <img
                                  className="wps_order_msg_attachment_thumbnail"
                                  src={file.img ? `${wps_rma_react_object.attachment_url + file.name}` : "attachment.png"}
                                  alt={file.name}
                                />
                                <span className="wps_order_msg_attachment_file_name">{file.name}</span>
                              </a>
                            </div>
                          ))}
                        </div>
                      </>
                    )}
                  </div>
                );
              })}
            </div>
            <form
              onSubmit={handleSubmit}
              encType="multipart/form-data"
              >
              <div class="wps-rma-order-msg-wrapper">
                <textarea
                  id="wps_order_new_msg"
                  name="wps_order_new_msg"
                  placeholder={`${wps_rma_react_object.textare_placeholder}`}
                  rows="1"
                  maxLength="10000"
                  required
                  value={formData.wps_order_new_msg}
                  onChange={handleChange}
                ></textarea>
                <div className="wps-order-msg-attachment-wrapper">
                    { 'on' == uploadAttach && (
                      <>
                      <div className="wps-order-attachment">
                        <div className={`wps_order_msg_att-wrap ${ ( formData.wps_order_msg_attachment && formData.wps_order_msg_attachment.length > 0 ) ? 'active' : 'not_active'} `}>
                          <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
                            <path d="M13 4H8.8C7.11984 4 6.27976 4 5.63803 4.32698C5.07354 4.6146 4.6146 5.07354 4.32698 5.63803C4 6.27976 4 7.11984 4 8.8V15.2C4 16.8802 4 17.7202 4.32698 18.362C4.6146 18.9265 5.07354 19.3854 5.63803 19.673C6.27976 20 7.11984 20 8.8 20H15.2C16.8802 20 17.7202 20 18.362 19.673C18.9265 19.3854 19.3854 18.9265 19.673 18.362C20 17.7202 20 16.8802 20 15.2V11" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 16L8.29289 11.7071C8.68342 11.3166 9.31658 11.3166 9.70711 11.7071L13 15M13 15L15.7929 12.2071C16.1834 11.8166 16.8166 11.8166 17.2071 12.2071L20 15M13 15L15.25 17.25" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18 8V3M18 3L16 5M18 3L20 5" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <input
                            type="file"
                            id="wps_order_msg_attachment"
                            name="wps_order_msg_attachment"
                            onChange={handleFileChange}
                            multiple
                          />
                        </div>
                      </div>
                      </>
                    )}
                  <div className="wps-order-msg-btn">
                      <input
                        type="submit"
                        id="wps_rma_order_message_react_submit"
                        name="wps_rma_order_message_react_submit"
                        value="Send"
                        data-id={orderID}
                      />
                      <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none">
                        <path d="M11.5003 12H5.41872M5.24634 12.7972L4.24158 15.7986C3.69128 17.4424 3.41613 18.2643 3.61359 18.7704C3.78506 19.21 4.15335 19.5432 4.6078 19.6701C5.13111 19.8161 5.92151 19.4604 7.50231 18.7491L17.6367 14.1886C19.1797 13.4942 19.9512 13.1471 20.1896 12.6648C20.3968 12.2458 20.3968 11.7541 20.1896 11.3351C19.9512 10.8529 19.1797 10.5057 17.6367 9.81135L7.48483 5.24303C5.90879 4.53382 5.12078 4.17921 4.59799 4.32468C4.14397 4.45101 3.77572 4.78336 3.60365 5.22209C3.40551 5.72728 3.67772 6.54741 4.22215 8.18767L5.24829 11.2793C5.34179 11.561 5.38855 11.7019 5.407 11.8459C5.42338 11.9738 5.42321 12.1032 5.40651 12.231C5.38768 12.375 5.34057 12.5157 5.24634 12.7972Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <input
                        type="hidden"
                        name="wps_order_msg_nonce"
                        value="your_nonce_here"
                      />
                  </div>
                </div>
                </div>
                {uploadAttach && (
                  <div className="wps_o_m-label-wrap">
                    <label>{wps_rma_react_object.attach_note}</label>
                  </div>
                )}
                  { wps_rma_react_object.is_admin != 'shop_manager' && 'on' == enableSmsForCustomer &&
                    'on' == enableSmsNotification &&
                    (
                      <div className="wps_rma_section wps_rma_notification" id="wps_rma_notification_div">
                        <label for="wps_rma_customer_contact_order_message">
                          {wps_rma_react_object.sms_label}
                          <input
                            type="tel"
                            name="wps_rma_customer_contact_order_message"
                            id="wps_rma_customer_contact_order_message"
                            onChange={handleChange}
                          />
                        </label>
                        <div className="wps_rma_notification_label">
                          {wps_rma_react_object.sms_example}
                        </div>
                      </div>
                  )}
            </form>
            </div>
      </Fragment>
    );
};