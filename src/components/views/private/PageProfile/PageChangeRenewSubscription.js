import { useState } from "react";
import { useHistory } from "react-router-dom";
import { Button, Card, Col, Collapse, Row, Typography } from "antd";
import { role } from "../../../providers/companyInfo";
import ModalDeactivateAcc from "./Components/ModalDeactivateAcc";

export default function PageChangeRenewSubscription() {
  const history = useHistory();

  const [toggleModalDeactivateAcc, setToggleModalDeactivateAcc] = useState({
    title: "",
    show: false,
  });

  return (
    <Card
      className="page-profile-subscription"
      id="PageChangeRenewSubscription"
    >
      <Row>
        <Col xs={24} sm={24} md={24} lg={24} xl={20} xxl={16}>
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
              header="CURRENT MEMBERSHIP"
              key="1"
              className="accordion bg-darkgray-form m-b-md border "
            >
              <Row gutter={[12, 12]}>
                <Col xs={24} sm={24} md={24}>
                  <Typography.Text>
                    Your current subscription type is:
                  </Typography.Text>
                  <br />
                  <Typography.Text strong className="color-6 font-600">
                    CANCER CAREGIVER - $25.00
                  </Typography.Text>
                </Col>
                <Col xs={24} sm={24} md={24}>
                  <Row gutter={[12, 12]}>
                    <Col xs={10} sm={10} md={8} lg={5}>
                      <Typography.Text>Created Date:</Typography.Text>
                    </Col>
                    <Col xs={14} sm={14} md={16} lg={19}>
                      <Typography.Text strong>2022-05-18</Typography.Text>
                    </Col>
                    <Col xs={10} sm={10} md={8} lg={5}>
                      <Typography.Text>Period Start:</Typography.Text>
                    </Col>
                    <Col xs={14} sm={14} md={16} lg={19}>
                      <Typography.Text strong>2022-05-18</Typography.Text>
                    </Col>
                    <Col xs={10} sm={10} md={8} lg={5}>
                      <Typography.Text>Period To:</Typography.Text>
                    </Col>
                    <Col xs={14} sm={14} md={16} lg={19}>
                      <Typography.Text strong>2022-06-18</Typography.Text>
                    </Col>
                    <Col xs={10} sm={10} md={8} lg={5}>
                      <Typography.Text>Collection Method:</Typography.Text>
                    </Col>
                    <Col xs={14} sm={14} md={16} lg={19}>
                      <Typography.Text strong>
                        <span>Auto Renew Enabled</span>
                        <br />
                        <span className="color-6">Disabled Auto Renew</span>
                      </Typography.Text>
                    </Col>
                  </Row>
                </Col>
                <Col xs={24} sm={24} md={24}>
                  <div className="btn-list">
                    <Button
                      size="large"
                      className="btn-primary w-100 b-r-none"
                      onClick={() =>
                        history.push("/profile/account/payment-and-invoices")
                      }
                    >
                      PAYMENTS & INVOICES
                    </Button>
                    <Button
                      size="large"
                      className="btn-warning w-100 b-r-none"
                      onClick={() =>
                        history.push(
                          "/profile/account/subscription/upgrade-subscription"
                        )
                      }
                    >
                      UPGRADE SUBSCRIPTION
                    </Button>
                    <Button
                      size="large"
                      className="btn-main-invert w-100 b-r-none"
                      onClick={() => {
                        let title = "";
                        if (role() === "Cancer CareGiver") {
                          title = "Cancer CareGiver $25";
                        } else {
                          title = "Cancer CareProfessional $75";
                        }
                        setToggleModalDeactivateAcc({ title, show: true });
                      }}
                    >
                      CANCEL SUBSCRIPTION
                    </Button>
                  </div>
                </Col>
              </Row>
            </Collapse.Panel>
          </Collapse>
        </Col>
      </Row>

      <ModalDeactivateAcc
        toggleModalDeactivateAcc={toggleModalDeactivateAcc}
        setToggleModalDeactivateAcc={setToggleModalDeactivateAcc}
      />
    </Card>
  );
}
