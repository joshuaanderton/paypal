import React from 'react'

const PayPalButton: React.FC<{ onClick: () => void, buttonText?: string }> = ({ onClick, buttonText }) => {
  return (
    <div>
      <button className="" type="button" onClick={onClick}>
        {buttonText || 'PayPal Button'}
      </button>
    </div>
  )
}
