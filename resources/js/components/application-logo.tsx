import { SVGAttributes } from "react";

export default function ApplicationLogo(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M12 2C12 2 8.5 3 7 5C5 8 6.5 10.5 6.5 10.5L3 15L9 14.5C9 14.5 11 16 12 18C13 16 15 14.5 15 14.5L21 15L17.5 10.5C17.5 10.5 19 8 17 5C15.5 3 12 2 12 2ZM12 4C13.1046 4 14 4.89543 14 6C14 7.10457 13.1046 8 12 8C10.8954 8 10 7.10457 10 6C10 4.89543 10.8954 4 12 4ZM6 16C4.89543 16 4 16.8954 4 18C4 19.1046 4.89543 20 6 20C7.10457 20 8 19.1046 8 18C8 16.8954 7.10457 16 6 16ZM18 16C16.8954 16 16 16.8954 16 18C16 19.1046 16.8954 20 18 20C19.1046 20 20 19.1046 20 18C20 16.8954 19.1046 16 18 16Z"
                fill="currentColor"
            />
        </svg>
    );
}
