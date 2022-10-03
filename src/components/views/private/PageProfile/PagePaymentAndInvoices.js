import { useState } from "react";
import { Card, Col, Collapse, Row, Typography } from "antd";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faFilePdf } from "@fortawesome/pro-solid-svg-icons";
import { role, userData } from "../../../providers/companyInfo";
import moment from "moment";
import { GET } from "../../../providers/useAxiosQuery";

export default function PagePaymentAndInvoices(props) {
  const { location } = props;

  // console.log("props", props);

  const [dataUserPayment, setDataUserPayment] = useState([]);
  const [invoiceData, setInvoiceData] = useState();

  GET(`api/v1/user_payment`, "user_payment_dashboard_list", (res) => {
    // console.log("user_payment_dashboard_list", res.data);
    if (res.data) {
      setDataUserPayment(res.data);
      if (location && location.state) {
        setInvoiceData(location.state);
      } else {
        setInvoiceData(res.data.length > 0 ? res.data[0] : {});
      }
    }
  });

  const handleShowInvoiceData = (record) => {
    setInvoiceData(record);
  };

  return (
    <Card className="page-payment-and-invoices" id="PagePaymentAndInvoices">
      <Row gutter={[12, 20]}>
        <Col xs={24} sm={24} md={24} lg={14}>
          {invoiceData && (
            <div className="invoices-account-template">
              <Row gutter={[12, 25]}>
                <Col xs={24} sm={24} md={24}>
                  <div>
                    <div>
                      <Typography.Title level={3} className="color-1 m-b-none">
                        {role()}
                      </Typography.Title>
                      <Typography.Text>
                        123 Someplace Ave., Chadler, AZ 85224
                      </Typography.Text>
                      <br />
                      <Typography.Text>(800) 123-4567</Typography.Text>
                      <br />
                      <Typography.Text>
                        billing@cancercaregivers.com
                      </Typography.Text>
                    </div>
                    <div>
                      <Typography.Title
                        level={3}
                        className="color-10 invoice-text"
                      >
                        INVOICE
                      </Typography.Title>
                    </div>
                  </div>
                </Col>
                <Col xs={24} sm={24} md={14}>
                  <Typography.Title level={4} className="color-1">
                    INVOICED TO
                  </Typography.Title>
                  <Typography.Title level={3} className="m-t-none m-b-none">
                    {userData().firstname} {userData().lastname}
                  </Typography.Title>
                  <Typography.Text>{userData().contact_number}</Typography.Text>
                  <br />
                  <Typography.Text>{userData().email}</Typography.Text>
                </Col>
                <Col xs={24} sm={24} md={10}>
                  <Typography.Title level={4} className="color-1">
                    INVOICED INFORMATION
                  </Typography.Title>
                  <div className="invoiced-information">
                    <Typography.Text>Invoice No.</Typography.Text>
                    <Typography.Text>#{invoiceData.invoice_id}</Typography.Text>
                  </div>
                  <div className="invoiced-information">
                    <Typography.Text>Date Paid</Typography.Text>
                    <Typography.Text>
                      {moment(invoiceData.date_paid).format("MM/DD/YYYY")}
                    </Typography.Text>
                  </div>
                </Col>
                <Col xs={14} sm={14} md={14}>
                  <Typography.Text>
                    Cancer Caregiver
                    {role() === "Cancer Care Professional"
                      ? " Employee"
                      : ""}{" "}
                    Certification
                  </Typography.Text>
                </Col>
                <Col xs={10} sm={10} md={10}>
                  <Typography.Text strong>
                    PAID ${invoiceData.amount}
                  </Typography.Text>
                </Col>
                <Col xs={24} sm={24} md={24}>
                  <Typography.Text italic>
                    Thank you for using Cancer CareGivers to receive your
                    certification. Please save this invoice for your records. To
                    download a pdf,{" "}
                    <Typography.Text strong className="color-6">
                      click here <FontAwesomeIcon icon={faFilePdf} />{" "}
                    </Typography.Text>
                  </Typography.Text>
                </Col>
              </Row>
            </div>
          )}
        </Col>

        <Col xs={24} sm={24} md={24} lg={10}>
          <Collapse
            className="main-1-collapse border-none"
            expandIcon={({ isActive }) =>
              isActive ? (
                <span
                  className="ant-menu-submenu-arrow"
                  style={{ color: "#FFF", transform: "rotate(270deg)" }}
                ></span>
              ) : (
                <span
                  className="ant-menu-submenu-arrow"
                  style={{ color: "#FFF", transform: "rotate(90deg)" }}
                ></span>
              )
            }
            defaultActiveKey={["1"]}
            expandIconPosition="start"
          >
            <Collapse.Panel
              header="RECENT INVOICES"
              key="1"
              className="accordion bg-darkgray-form m-b-md border collapse-recent-invoices"
            >
              <table className="table table-striped m-b-n">
                <thead>
                  <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  {dataUserPayment.map((item, index) => {
                    return (
                      <tr key={index}>
                        <td>
                          <Typography.Link
                            className="color-6"
                            onClick={() => handleShowInvoiceData(item)}
                          >
                            #{item.invoice_id}
                          </Typography.Link>
                        </td>
                        <td>
                          {moment(item.date_paid).format("MMMM DD, YYYY")}
                        </td>
                        <td>${item.amount}</td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </Collapse.Panel>
          </Collapse>
        </Col>
      </Row>
    </Card>
  );
}
