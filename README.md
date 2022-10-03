# Cancer Caregivers

This theme is for 5Pints usage/distribution. Any other usage of this is not allowed.

<!-- Form Select -->
<!--
    <Form.Item
        className="form-select-error"
        name="event_type"
        rules={[validator.require]}
        hasFeedback
    >
        <FloatSelect
            label="Select Live In-Person or Virtual Event"
            placeholder="Select Live In-Person or Virtual Event"
            options={[
            {
                    label: "Live In-Person",
                    value: "Live In-Person",
                },
                {
                    label: "Virtual Event",
                    value: "Virtual Event",
                },
            ]}
        />
    </Form.Item>
-->

<!-- Form Select Multiple-->
<!--
    <Form.Item
        className="form-select-error-multi"
        name="event_type"
        rules={[validator.require]}
        hasFeedback
    >
        <FloatSelect
            label="Select Live In-Person or Virtual Event"
            placeholder="Select Live In-Person or Virtual Event"
            options={[
            {
                    label: "Live In-Person",
                    value: "Live In-Person",
                },
                {
                    label: "Virtual Event",
                    value: "Virtual Event",
                },
            ]}
            multi="multiple"
        />
    </Form.Item>
-->

<!-- Float Input Mask -->
<!-- <Form.Item
        name="cell_number"
        rules={[validator.require]}
        hasFeedback
        >
        <FloatInputMask
            label="Cell Phone"
            placeholder="Cell Phone"
            maskLabel="cell_phone"
            maskType="999-999-9999"
        />
    </Form.Item>
-->

<!-- icons -->
<!--
    https://react-icons.github.io/react-icons/
 -->

<!-- Text Area -->
<!--
    <Form.Item
        name="additional_legal_credentials"
        rules={[validator.require]}
        className="input-text-area-label"
    >
        <FloatTextArea
            label="Additional Legal Credentials"
            placeholder="Additional Legal Credentials"
        />
    </Form.Item>{" "}
 -->

<!-- empty blank page -->
<!--

import { Card, Col, Row, Collapse } from "antd";
import ComponentHeader from "../Components/ComponentHeader";
import { faUser } from "@fortawesome/pro-solid-svg-icons";

export default function PageFaqs({ match, permission }) {
  const { Panel } = Collapse;
  return (
    <>
      <ComponentHeader title={permission} sub_title="SEARCH" icon={faUser} />

      <Card>
        <Row gutter={12}>
          <Col span={24}>
            <Collapse
              className="ant-collapse-primary"
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
              expandIconPosition="right"
              defaultActiveKey={["1"]}
            >
              <Panel
                header="Login Information"
                key="1"
                className="accordion bg-darkgray-form"
                extra={
                    <FontAwesomeIcon
                        onClick={() => remove(name)}
                        icon={faTrash}
                    />
                }
              >
                asdjkhds
              </Panel>
            </Collapse>
          </Col>
        </Row>
      </Card>
    </>
  );
}

-->

<!-- Left icon collapse -->

<!--

<Collapse
    className="main-1-collapse"
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
    <Panel
        header="LOGIN INFORMATION"
        key="1"
        className="accordion bg-darkgray-form"
        extra={
            <FontAwesomeIcon
                icon={faTrash}
                onClick={(event) => {
                    alert("asd");
                }}
            />
        }
    >
        <span>
        Yes, we offer testing packages for all ages and sports.
        </span>
    </Panel>
</Collapse>









-->
