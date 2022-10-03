import {
  Button,
  Card,
  Checkbox,
  Col,
  Collapse,
  Form,
  notification,
  Radio,
  Row,
  Typography,
} from "antd";
import { useState } from "react";
import { encrypt, role, userData } from "../../../providers/companyInfo";
// import FloatSelect from "../../../providers/FloatSelect";
import FloatSelectWithDangerouslySetInnerHTML from "../../../providers/FloatSelectWithDangerouslySetInnerHTML";
import { GET, POST } from "../../../providers/useAxiosQuery";

export default function PageUpgradeSubscription() {
  const [form] = Form.useForm();
  const [programTypes, setProgramTypes] = useState([]);
  const [selectedProgramType, setSelectedProgramType] = useState();

  GET("api/v1/acc_type", "acc_type", (res) => {
    if (res.success) {
      // console.log("acc_type", res.data);
      let data = [];

      res.data.map((item) => {
        data.push({
          label: item.description,
          value: item.id,
          policy: item.privacy && item.privacy.privacy_policy,
          amount:
            item.account_plan && item.account_plan.length > 0
              ? item.account_plan[0].amount
              : 0,
        });

        return "";
      });

      // console.log("acc_type data", data);
      setProgramTypes(data);
    }
  });

  const { mutate: mutateSubscription, isLoading: isLoadingSubscription } = POST(
    "api/v1/user_plan",
    "user_plan_subscription"
  );

  const [checkboxYes, setCheckboxYes] = useState(true);
  const handleScroll = (e) => {
    // console.log("values");
    let element = e.target;
    // console.log("element.scrollHeight", element.scrollHeight);
    // console.log("element.scrollTop", element.scrollTop);
    // console.log("element.clientHeight", element.clientHeight);

    if (element.scrollHeight - element.scrollTop <= element.clientHeight) {
      setCheckboxYes(false);
    } else {
      setCheckboxYes(true);
    }
  };

  const [checkboxYesStatus, setCheckboxYesStatus] = useState(false);
  const onChangeCheckbox = (e) => {
    // console.log("e.target.checked", e.target.checked);
    setCheckboxYesStatus(e.target.checked);
  };

  const onFinishSubscription = (values) => {
    let data = { ...values, user_id: userData().id };
    // console.log("data", data);

    mutateSubscription(data, {
      onSuccess: (res) => {
        if (res.success) {
          notification.success({
            message: "Subscription",
            description: res.message,
          });

          let data = res.data;

          localStorage.userdata = encrypt({
            ...data,
          });
        } else {
          notification.error({
            message: "Subscription",
            description: res.message,
          });
        }
      },
      onError: (err) => {
        notification.error({
          message: "Subscription",
          description: err.response.data.message,
        });
      },
    });
  };

  return (
    <Card id="PageUpgradeSubscription">
      <Form form={form} onFinish={onFinishSubscription}>
        <Row>
          <Col xs={24} sm={24} md={24} lg={18} xl={14}>
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
                header="SELECT SUBSCRIPTION TYPE"
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
                      {role() === "Cancer Caregiver"
                        ? "CANCER CAREGIVER - $25.00"
                        : "Cancer Care Professional $75"}
                    </Typography.Text>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="type">
                      <FloatSelectWithDangerouslySetInnerHTML
                        label="Select Subscription"
                        placeholder="Select Subscription"
                        options={programTypes}
                        onChange={(e) => {
                          let val = programTypes.filter((x) => x.value === e);
                          // console.log("val", val);
                          setSelectedProgramType({
                            ...val[0],
                            coupon_apply: 0,
                            coupon: "",
                            message: "",
                          });
                        }}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <div
                      className="flex gap10"
                      style={{ alignItems: "center" }}
                    >
                      <div>
                        <Typography.Text>
                          Are you also the patient?{" "}
                        </Typography.Text>
                      </div>
                      <div>
                        <Form.Item name="is_patient" noStyle>
                          <Radio.Group>
                            <Radio value={0}>No</Radio>
                            <Radio value={1}>Yes</Radio>
                          </Radio.Group>
                        </Form.Item>
                      </div>
                    </div>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Typography.Text strong>
                      TOTAL AMOUNT $
                      {selectedProgramType ? selectedProgramType.amount : 0}
                    </Typography.Text>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            {selectedProgramType && (
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
                  header="SELECT SUBSCRIPTION TYPE"
                  key="1"
                  className="accordion bg-darkgray-form m-b-md border "
                >
                  <Row gutter={[12, 12]}>
                    <Col xs={24} sm={24} md={24}>
                      <Typography.Text className="color-6" strong>
                        Please read/scroll to the end to continue.
                      </Typography.Text>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
                      <div
                        onScroll={handleScroll}
                        className="scrollbar-2"
                        style={{
                          marginBottom: 10,
                          marginTop: 10,
                          height: 100,
                          resize: "vertical",

                          overflow: "auto",
                          border: "1px solid #58585a",
                        }}
                        dangerouslySetInnerHTML={{
                          __html:
                            selectedProgramType && selectedProgramType.policy,
                        }}
                      ></div>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
                      <Checkbox
                        onChange={onChangeCheckbox}
                        name="checkbox_2"
                        className="checkbox_yes"
                        disabled={checkboxYes}
                      >
                        Yes, I have read the Privacy Policy and Terms and
                        Conditions
                      </Checkbox>
                    </Col>
                  </Row>
                </Collapse.Panel>
              </Collapse>
            )}

            <Button
              type="primary"
              htmlType="submit"
              loading={isLoadingSubscription}
              className="btn-main-invert-outline b-r-none"
              block
              size="large"
              disabled={checkboxYes ? true : checkboxYesStatus ? false : true}
            >
              COMPLETE PURCHASE
            </Button>
          </Col>
        </Row>
      </Form>
    </Card>
  );
}
