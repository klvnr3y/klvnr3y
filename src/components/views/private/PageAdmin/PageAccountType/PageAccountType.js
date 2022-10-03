import { useEffect, useState } from "react";
import {
  Button,
  Card,
  Col,
  Collapse,
  Form,
  notification,
  Row,
  Tabs,
} from "antd";
import { SaveOutlined } from "@ant-design/icons";

import FloatInput from "../../../../providers/FloatInput";

import {
  formats,
  modulesToolBarV2,
} from "../../../../providers/reactQuillOptions";

import { GETMANUAL, POST } from "../../../../providers/useAxiosQuery";
import TableAccountTypesPlan from "./Components/TableAccountTypesPlan";

import ReactQuill, { Quill } from "react-quill";
import "react-quill/dist/quill.snow.css";
import ImageResize from "quill-image-resize-module-react";
import TableFaqs from "./Components/TableFaqs";
Quill.register("modules/imageResize", ImageResize);

const validator = {
  require: {
    required: true,
    message: "Required",
  },
};

export default function PageAccountType(props) {
  const { match } = props;

  const [form] = Form.useForm();
  const [selectedData, setSelectedData] = useState();

  const getACID = (type) => {
    if (type === "caregivers") {
      return 1;
    } else if (type === "careprofessional") {
      return 2;
    }
  };
  let type = match.url.split("/");
  let id = getACID(type[2]);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      refetchAccountTypes();
    });
    return () => {
      clearTimeout(timeoutId);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const {
    // data: dataGetAccountType,
    // isLoading: isLoadingGetAccountType,
    refetch: refetchAccountTypes,
  } = GETMANUAL(`api/v1/account_type/${id}`, "account_types", (res) => {
    // console.log("dataGetAccountType", res);
    if (res.data) {
      let data = res.data;
      setSelectedData(data);
      form.setFieldsValue({
        type: data.type,
        description: data.description ? data.description : "",
        privacy_policy:
          data.privacy && data.privacy.privacy_policy !== ""
            ? data.privacy.privacy_policy
            : "",
      });

      if (data.privacy && data.privacy.privacy_policy !== "") {
        // console.log("sssssssssss");
      }
      if (data.privacy && data.privacy.privacy_policy === "") {
        form.resetFields(["privacy_policy"]);
      }
    }
  });

  const {
    mutate: mutateCreateAccountType,
    isLoading: isLoadingCreateAccountType,
  } = POST("api/v1/account_type", "account_type_create_update");

  const onFinish = (values) => {
    let data = {
      ...values,
      id: selectedData ? selectedData.id : "",
      privacy_id:
        selectedData && selectedData.privacy ? selectedData.privacy.id : "",
    };
    mutateCreateAccountType(data, {
      onSuccess: (res) => {
        if (res.success) {
          setSelectedData(res.data);

          notification.success({
            message: "Account Type",
            description: res.message,
          });
        }
      },
      onError: (err) => {
        notification.error({
          message: "Account Type",
          description: err.response.data.message,
        });
      },
    });
  };

  return (
    <Card className="page-admin-account-type" id="PageAccountType">
      <Tabs
        defaultActiveKey="1"
        type="card"
        size="large"
        className="account-types"
      >
        <Tabs.TabPane tab="Account Type and Policy" key="1">
          <Form
            form={form}
            onFinish={onFinish}
            wrapperCol={{ span: 24 }}
            layout="vertical"
            initialValues={{
              description: "",
              privacy_policy: "",
            }}
          >
            <Row gutter={[12, 12]}>
              <Col xs={24} sm={24} md={24} lg={24} xl={14}>
                <Collapse
                  defaultActiveKey={["1"]}
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
                  expandIconPosition="end"
                  className="ant-collapse-primary border-none collapse-account-type"
                >
                  <Collapse.Panel header="Account Type Details" key="1">
                    <Form.Item
                      name="type"
                      rules={[validator.require]}
                      className="capslock"
                      hasFeedback
                    >
                      <FloatInput
                        label="Account Type"
                        placeholder="Account Type"
                      />
                    </Form.Item>

                    <Form.Item
                      name="description"
                      rules={[validator.require]}
                      // className="quill-input"
                    >
                      <ReactQuill
                        // className="ticket-quill"
                        theme="snow"
                        style={{ height: 200 }}
                        formats={formats}
                        modules={modulesToolBarV2}
                        placeholder="Description"
                      />
                    </Form.Item>
                    <br />
                  </Collapse.Panel>
                </Collapse>
              </Col>
              <Col xs={24} sm={24} md={24} lg={24} xl={10}>
                <Collapse
                  defaultActiveKey={["1"]}
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
                  expandIconPosition="end"
                  className="ant-collapse-primary collapse-account-type"
                >
                  <Collapse.Panel header="Privacy Policy" key="1">
                    <Form.Item
                      name="privacy_policy"
                      rules={[validator.require]}
                      // className="quill-input"
                    >
                      <ReactQuill
                        // className="ticket-quill"
                        theme="snow"
                        style={{ height: 200 }}
                        formats={formats}
                        modules={modulesToolBarV2}
                        placeholder="Policy"
                      />
                    </Form.Item>
                    <br />
                  </Collapse.Panel>
                </Collapse>
              </Col>
              <Col xs={24} sm={24} md={24} lg={24} xl={12}>
                <Button
                  size="large"
                  className="btn-main-invert-outline b-r-none p-l-lg p-r-lg"
                  icon={<SaveOutlined />}
                  onClick={() => form.submit()}
                  loading={isLoadingCreateAccountType}
                >
                  Submit
                </Button>
              </Col>
            </Row>
          </Form>
        </Tabs.TabPane>

        <Tabs.TabPane tab="Plans" key="2">
          <TableAccountTypesPlan id={id} />
        </Tabs.TabPane>

        <Tabs.TabPane tab="FAQ's" key="3">
          <TableFaqs id={id} />
        </Tabs.TabPane>
      </Tabs>
    </Card>
  );
}
