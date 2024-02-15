import React from "react"
import { PayPalScriptProvider, PayPalButtons, ReactPayPalScriptOptions } from "@paypal/react-paypal-js"

interface Props {
  currency: string
  onCreateOrder: () => Promise<string>
  onCaptureOrder: () => void
  className?: string
}

const PayPalButton: React.FC<Props> = ({ currency, onCreateOrder, onCaptureOrder, className }) => {

  const initialOptions: ReactPayPalScriptOptions = {
    clientId: import.meta.env.VITE_PAYPAL_CLIENT_ID,
    currency: currency,
    disableFunding: "paylater,venmo,card",
    dataSdkIntegrationSource: "integrationbuilder_sc",
  }

  return (
    <div className={className}>
      <PayPalScriptProvider options={initialOptions}>
        <div className="flex flex-col items-stretch [&>div]:flex [&>div]:flex-col w-full rounded-[12px] overflow-hidden">
          <PayPalButtons
            style={{
              shape: "rect",
              layout: "vertical",
            }}
            createOrder={onCreateOrder}
            onApprove={async () => onCaptureOrder()}
          />
        </div>
      </PayPalScriptProvider>
    </div>
  )
}

export default PayPalButton
